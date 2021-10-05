<?php
declare(strict_types=1);

namespace BunnyPHP;
defined('BUNNY_PATH') or define('BUNNY_PATH', __DIR__);

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class BunnyPHP
{
    const BUNNY_VERSION = '3.0.2';
    const MODE_NORMAL = 0;
    const MODE_API = 1;
    const MODE_AJAX = 2;
    const MODE_CLI = 3;

    /**
     * @var $config Config
     */
    protected static Config $config;
    protected int $mode = BunnyPHP::MODE_NORMAL;
    protected array $apps = [];

    private static BunnyPHP $instance;
    private static ?Database $db = null;
    private static ?Storage $storage = null;
    private static ?Cache $cache = null;
    private static ?Request $request = null;
    private static ?Logger $logger = null;

    private array $variable = [];
    private array $container = [];
    private array $path = [];

    public function __construct(int $m = BunnyPHP::MODE_NORMAL)
    {
        $this->mode = $m;
        BunnyPHP::$instance = $this;
    }

    public function run()
    {
        spl_autoload_register([$this, 'loadClass']);
        set_error_handler([$this, 'handleErr']);
        $this->setReporting();
        $this->loadConfig();
        $this->route();
    }

    private function route()
    {
        $appName = '';
        $defCtr = self::$config->get('controller', 'Index');
        if ($this->mode == BunnyPHP::MODE_CLI) {
            $cli_arg = array_slice($_SERVER['argv'], 1);
            if (in_array($cli_arg[0], array_keys($this->apps))) {
                $appName = $cli_arg[0];
                array_shift($cli_arg);
            }
            $controllerName = ucfirst($cli_arg[0] ?? $defCtr);
            $actionName = $cli_arg[1] ?? 'index';
            $param = array_slice($cli_arg, 2);
        } else {
            $controllerName = ucfirst(!empty($_GET['mod']) ? $_GET['mod'] : $defCtr);
            $actionName = !empty($_GET['action']) ? $_GET['action'] : 'index';
            $request_url = $_SERVER['REQUEST_URI'];
            $position = strpos($request_url, '?');
            $request_url = ($position === false) ? $request_url : substr($request_url, 0, $position);
            $request_url = trim($request_url, '/');
            $param = [];
            if ($request_url && !in_array(strtolower($request_url), ['index.php', 'api.php', 'ajax.php'])) {
                $url_array = explode('/', $request_url);
                $url_array = array_filter($url_array);
                $mod = strtolower($url_array[0]);
                if ($mod === 'api' || $mod === 'api.php') {
                    array_shift($url_array);
                    $this->mode = BunnyPHP::MODE_API;
                } elseif ($mod === 'ajax' || $mod === 'ajax.php') {
                    array_shift($url_array);
                    $this->mode = BunnyPHP::MODE_AJAX;
                } elseif ($mod === 'index.php') {
                    array_shift($url_array);
                }
                if (isset($url_array[0]) && in_array($url_array[0], array_keys($this->apps))) {
                    $appName = $url_array[0];
                    array_shift($url_array);
                }
                $controllerName = ucfirst($url_array[0] ?? $controllerName);
                array_shift($url_array);
                $actionName = $url_array[0] ?? $actionName;
                array_shift($url_array);
                $param = $url_array;
            }
        }
        $prefix = TP_NAMESPACE;
        $this->path = $param;
        if (!empty($appName)) {
            self::set('app', $appName);
            $appConf = $this->apps[$appName];
            $prefix = $appConf['namespace'] ?? '';
            if (isset($appConf['path'])) define('SUB_APP_PATH', $appConf['path']);
        }
        if (!empty($prefix)) {
            $controllerPrefix = $prefix . '\\Controller\\';
        } else {
            $controllerPrefix = '';
        }
        define('BUNNY_APP', $prefix);
        define('BUNNY_APP_MODE', $this->mode);
        define('BUNNY_CONTROLLER', $controllerName);
        define('BUNNY_ACTION', $controllerName);
        $controller = $controllerPrefix . $controllerName . 'Controller';
        if (!class_exists($controller)) {
            if (!class_exists($controllerPrefix . 'OtherController')) {
                View::error(['ret' => '-2', 'status' => 'mod does not exist', 'bunny_error' => Language::get('mod_not_exists', ['mod' => $controller])], $this->mode);
            } else {
                $controller = $controllerPrefix . 'OtherController';
            }
        }
        $request_method = strtolower($_SERVER['REQUEST_METHOD'] ?? 'cli');
        $this->dispatch($controller, $actionName, $request_method);
    }

    private function dispatch($controller, $action, $requestMethod)
    {
        try {
            $class = new ReflectionClass($controller);
            $assignedValue = [];
            $paramContext = [];
            $result = $this->runAttrFilter($class, $assignedValue);
            if ($result === Filter::STOP) return;
            if ($classDocComment = $class->getDocComment()) {
                $classDoc = $this->processDocComment($classDocComment);
                if (isset($classDoc['filter'])) {
                    $result = $this->runFilter($classDoc['filter'], $assignedValue);
                    if ($result === Filter::STOP) return;
                }
            }
            if (!$class->isInstantiable()) View::error([], $this->mode);
            $constructor = $class->getConstructor();
            if ($constructor) {
                if ($methodDocComment = $constructor->getDocComment()) {
                    $methodDoc = $this->processDocComment($methodDocComment);
                    if (isset($methodDoc['param'])) {
                        $this->processParamContext($methodDoc['param'], $paramContext);
                    }
                }
            }
            $instance = $class->newInstanceArgs($this->inject($constructor, $paramContext, true));
            $method = null;
            if ($class->hasMethod("ac_{$action}_{$requestMethod}")) {
                $method = $class->getMethod("ac_{$action}_{$requestMethod}");
            } else if ($class->hasMethod("ac_{$action}")) {
                $method = $class->getMethod("ac_{$action}");
            } else if ($class->hasMethod('other')) {
                $method = $class->getMethod('other');
            }
            if (!$method) View::error(['ret' => '-3', 'status' => 'action does not exist', 'bunny_error' => Language::get('action_not_exists', ['action' => $action])], $this->mode);
            $result = $this->runAttrFilter($method, $assignedValue);
            if ($result === Filter::STOP) return;
            if ($methodDocComment = $method->getDocComment()) {
                $methodDoc = $this->processDocComment($methodDocComment);
                if (isset($methodDoc['filter'])) {
                    $result = $this->runFilter($methodDoc['filter'], $assignedValue);
                    if ($result === Filter::STOP) return;
                }
                if (isset($methodDoc['param'])) {
                    $this->processParamContext($methodDoc['param'], $paramContext);
                }
            }
            if ($class->hasMethod('assignAll')) {
                $instance->assignAll($assignedValue);
            }
            $result = $method->invokeArgs($instance, $this->inject($method, $paramContext, true));
            if (is_array($result) || is_object($result)) {
                View::json($result);
            }
        } catch (ReflectionException $e) {
            View::error([], $this->mode);
        }
    }

    private function processParamContext($params, &$paramContext)
    {
        $patName = '/\$([\w]+)\s*/';
        $patValue = '/(path|header|request|config)\(([^)]*)\)/';
        $result = [];
        foreach ($params as $paramInfo) {
            if (preg_match($patName, $paramInfo, $matName)) {
                $name = trim($matName[1]);
                if (preg_match_all($patValue, $paramInfo, $matAll)) {
                    foreach ($matAll[1] as $i => $type) {
                        $key = trim($matAll[2][$i]);
                        $tmpVal = null;
                        if ($type === 'path') {
                            if ($key !== '') {
                                $keys = explode(',', $key);
                                $pos = intval(trim($keys[0]));
                                $tmpVal = $this->path[$pos] ?? ($keys[1] ?? null);
                            } else {
                                $tmpVal = $this->path;
                            }
                        } elseif ($type === 'header') {
                            $tmpVal = self::getRequest()->getHeader($key ?? $name);
                        } elseif ($type === 'config') {
                            $tmpVal = self::$config->get($key);
                        }
                        if ($tmpVal !== null) $result[$name] = $tmpVal;
                    }
                }
            }
        }
        $paramContext = array_merge($paramContext, $result);
    }

    private function runAttrFilter($reflect, &$assignedValue): int
    {
        if (method_exists($reflect, 'getAttributes')) {
            $filters = $reflect->getAttributes();
            foreach ($filters as $filter) {
                $f = $filter->newInstance();
                if ($f instanceof Filter) {
                    $result = $f->doFilter($filter->getArguments());
                    if ($result === Filter::STOP) return Filter::STOP;
                    $assignedValue = array_merge($assignedValue, $f->getVariable());
                }
            }
        }
        return Filter::NEXT;
    }

    private function runFilter($filters, &$assignedValue): int
    {
        foreach ($filters as $filterInfo) {
            /**
             * @var $filter Filter
             */
            $filterInfo = explode(' ', trim($filterInfo));
            array_filter($filterInfo);
            $filterName = trim(array_shift($filterInfo));
            $filterName = self::getClassName($filterName, 'filter');
            $filter = new $filterName($filterInfo);
            $result = $filter->doFilter($filterInfo);
            if ($result === Filter::STOP) return Filter::STOP;
            $assignedValue = array_merge($assignedValue, $filter->getVariable());
        }
        return Filter::NEXT;
    }

    private function createContainer($type): ?object
    {
        try {
            $class = new ReflectionClass($type);
            if (!$class->isInstantiable()) return null;
            return $class->newInstanceArgs($this->inject($class->getConstructor()));
        } catch (ReflectionException $e) {
            return null;
        }
    }

    public static function processDocComment($docComment): array
    {
        $pattern = '#(@([a-zA-Z]+)\s*([a-zA-Z0-9, ()_].*))#';
        $values = [];
        if (preg_match_all($pattern, $docComment, $matches, PREG_PATTERN_ORDER)) {
            foreach ($matches[2] as $i => $decorate) {
                $values[$decorate][] = rtrim($matches[3][$i], "\r\n");
            }
        }
        return $values;
    }

    /**
     * @throws ReflectionException
     */
    private function inject(?ReflectionMethod $method, $paramContext = [], $useRequest = false): array
    {
        $value = [];
        if (!$method) return $value;
        $REQ_TYPE = ['array', 'int', 'float', 'bool', 'string', ''];
        $AUTO_CONVERT_TYPE = ['int', 'float', 'bool'];
        if ($method->getNumberOfParameters() > 0) {
            $params = $method->getParameters();
            foreach ($params as $param) {
                $paramType = $param->getType();
                $type = ($paramType !== null && method_exists($paramType, 'getName')) ? $paramType->getName() : '';
                $name = '' . $param->getName();
                $defVal = $param->isOptional() ? $param->getDefaultValue() : '';
                $attrVal = null;
                if (method_exists($param, 'getAttributes')) {
                    $attrs = $param->getAttributes();
                    foreach ($attrs as $attr) {
                        $p = $attr->newInstance();
                        if ($p instanceof BaseParam) $attrVal = $p->value();
                    }
                }
                if (in_array($type, $REQ_TYPE)) {
                    $val = $attrVal ?? $paramContext[$name] ?? ($useRequest ? ($_REQUEST[$name] ?? $defVal) : $defVal);
                    if (in_array($type, $AUTO_CONVERT_TYPE)) $value[] = ($type . 'val')($val);
                    elseif ($type == 'array' && !is_array($val)) $val = [];
                    $value[] = $val;
                } else {
                    if (!isset($this->container[$type])) {
                        $this->container[$type] = $this->createContainer($type);
                    }
                    $value[] = $this->container[$type];
                }
            }
        }
        return $value;
    }

    public function get($key)
    {
        return $this->variable[$key] ?? null;
    }

    public function set($key, $value)
    {
        $this->variable[$key] = $value;
    }

    public static function app(): BunnyPHP
    {
        return self::$instance;
    }

    public static function getPath(): array
    {
        return self::app()->path;
    }

    public static function getDatabase(): Database
    {
        if (self::$db === null) {
            $dbName = '\\BunnyPHP\\PdoDatabase';
            if (self::$config->has('db')) {
                $name = self::$config->get('db.name');
                if ($name) {
                    $dbName = self::getClassName($name, 'database');
                }
            }
            self::$db = new $dbName(self::$config->get('db'), []);
        }
        return self::$db;
    }

    public static function getStorage(): Storage
    {
        if (self::$storage === null) {
            $storageName = '\\BunnyPHP\\FileStorage';
            if (self::$config->has('storage')) {
                $name = self::$config->get('storage.name');
                if ($name) {
                    $storageName = self::getClassName($name, 'storage');
                }
            }
            self::$storage = new $storageName(self::$config->get('storage', []));
        }
        return self::$storage;
    }

    public static function getCache(): Cache
    {
        if (self::$cache === null) {
            $cacheName = '\\BunnyPHP\\FileCache';
            if (self::$config->has('cache')) {
                $name = self::$config->get('cache.name');
                if ($name) {
                    $cacheName = self::getClassName($name, 'cache');
                }
            }
            self::$cache = new $cacheName(self::$config->get('cache', []));
        }
        return self::$cache;
    }

    public static function getRequest(): Request
    {
        return self::$request;
    }

    public static function getLogger(): Logger
    {
        if (self::$logger === null) {
            $loggerName = '\\BunnyPHP\\FileLogger';
            if (self::$config->has('logger')) {
                $name = self::$config->get('logger.name');
                if ($name) {
                    $loggerName = self::getClassName($name, 'logger');
                }
            }
            self::$logger = new $loggerName(self::$config->get('logger', []));
        }
        return self::$logger;
    }

    private function setReporting()
    {
        if (APP_DEBUG === true) {
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 'Off');
            ini_set('log_errors', 'On');
        }
    }

    private function loadConfig()
    {
        self::$config = Config::load('config');
        define('TP_SITE_NAME', self::$config->get('site_name', 'BunnyPHP'));
        define('TP_SITE_URL', self::$config->get('site_url', 'localhost'));
        define('TP_SITE_REWRITE', self::$config->get('site_rewrite', true));

        if (!defined('TP_NAMESPACE')) define('TP_NAMESPACE', self::$config->get('namespace', ''));

        if (self::$config->has('db')) {
            define('DB_PREFIX', self::$config->get('db.prefix'));
        }

        if (self::$config->has('apps')) {
            $this->apps = self::$config->get('apps', []);
        }
        BunnyPHP::$request = new Request();
    }

    public function handleErr($err_no, $err_str, $err_file, $err_line): bool
    {
        $err = ['ret' => '-8', 'status' => 'internal error', 'bunny_error' => "$err_str\nNo: $err_no\nFile: $err_file\nLine: $err_line"];
        if (APP_DEBUG) {
            $trace = debug_backtrace();
            array_shift($trace);
            $err['bunny_error_trace'] = $trace;
        }
        View::error($err, $this->mode);
        return false;
    }

    private static function loadClass($class)
    {
        list($shortName, $type) = self::getClassNameInfo($class);
        $frameworkFile = BUNNY_PATH . '/' . $shortName . '.php';
        $classType = ($type) ? strtolower($type) : strtolower(self::getClassType($shortName));
        $classFile = APP_PATH . 'app' . '/' . $classType . '/' . $shortName . '.php';
        if (file_exists($frameworkFile)) {
            include $frameworkFile;
        } elseif (defined('SUB_APP_PATH')) {
            $subClassFile = APP_PATH . 'app' . '/' . SUB_APP_PATH . '/' . $classType . '/' . $shortName . '.php';
            if (file_exists($subClassFile)) {
                include $subClassFile;
            } elseif (file_exists($classFile)) {
                include $classFile;
            }
        } elseif (file_exists($classFile)) {
            include $classFile;
        }
    }

    private static function getClassType($class): string
    {
        $i = strlen($class) - 1;
        while ($i >= 0 && ($class[$i] < 'A' || $class[$i] > 'Z')) {
            $i--;
        }
        return substr($class, $i);
    }


    public static function getClassName($class, $type = '', $base = TP_NAMESPACE): string
    {
        $tmp = explode('.', $class);
        $shortName = ucfirst(array_pop($tmp));
        $prefix = '';
        for ($i = 0; $i < count($tmp); $i++) {
            $prefix .= '\\' . ucfirst($tmp[$i]);
        }
        if ($prefix) {
            return $prefix . '\\' . ($type ? (ucfirst($type) . '\\') : '') . $shortName . ucfirst($type);
        } elseif ($base) {
            return $base . '\\' . ($type ? (ucfirst($type) . '\\') : '') . $shortName . ucfirst($type);
        } else {
            return $shortName . ucfirst($type);
        }
    }

    private static function getClassNameInfo($class): array
    {
        $tmp = explode('\\', $class);
        $shortName = array_pop($tmp);
        $type = array_pop($tmp);
        $prefix = implode('\\', $tmp);
        return [$shortName, $type, $prefix];
    }
}