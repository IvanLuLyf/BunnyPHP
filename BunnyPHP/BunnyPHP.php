<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/28
 * Time: 17:42
 */

namespace BunnyPHP;
defined('BUNNY_PATH') or define('BUNNY_PATH', __DIR__);

use ReflectionClass;
use ReflectionException;

class BunnyPHP
{
    const BUNNY_VERSION = '2.3.2';
    const MODE_NORMAL = 0;
    const MODE_API = 1;
    const MODE_AJAX = 2;
    const MODE_CLI = 3;

    /**
     * @var $config Config
     */
    protected static $config;
    protected $mode = BunnyPHP::MODE_NORMAL;
    protected $apps = [];

    private static $app;
    private static $storage;
    private static $cache;
    private static $request;
    private static $logger;

    private $variable = [];
    private $container = [];

    public function __construct($m = BunnyPHP::MODE_NORMAL)
    {
        $this->mode = $m;
        BunnyPHP::$app = $this;
    }

    public function run()
    {
        spl_autoload_register([$this, 'loadClass']);
        $this->setReporting();
        $this->loadConfig();
        $this->route();
    }

    private function route()
    {
        $appName = '';
        if ($this->mode == BunnyPHP::MODE_CLI) {
            $cli_arg = array_slice($_SERVER['argv'], 1);
            if (in_array($cli_arg[0], array_keys($this->apps))) {
                $appName = $cli_arg[0];
                array_shift($cli_arg);
            }
            $controllerName = !empty($cli_arg[0]) ? ucfirst($cli_arg[0]) : self::$config->get('controller', 'Index');
            $actionName = !empty($cli_arg[1]) ? $cli_arg[1] : 'index';
            $param = array_slice($cli_arg, 2);
        } else {
            $controllerName = !empty($_GET['mod']) ? ucfirst($_GET['mod']) : self::$config->get('controller', 'Index');
            $actionName = !empty($_GET['action']) ? $_GET['action'] : 'index';
            $request_url = $_SERVER['REQUEST_URI'];
            $position = strpos($request_url, '?');
            $request_url = ($position === false) ? $request_url : substr($request_url, 0, $position);
            $request_url = trim($request_url, '/');
            $param = [];
            if ($request_url && !in_array(strtolower($request_url), ['index.php', 'api.php', 'ajax.php'])) {
                $url_array = explode('/', $request_url);
                $url_array = array_filter($url_array);
                if (strtolower($url_array[0]) == "api" || strtolower($url_array[0]) == "api.php") {
                    array_shift($url_array);
                    $this->mode = BunnyPHP::MODE_API;
                } elseif (strtolower($url_array[0]) == "ajax" || strtolower($url_array[0]) == "ajax.php") {
                    array_shift($url_array);
                    $this->mode = BunnyPHP::MODE_AJAX;
                } elseif (strtolower($url_array[0]) == "index.php") {
                    array_shift($url_array);
                }
                if (isset($url_array[0]) && in_array($url_array[0], array_keys($this->apps))) {
                    $appName = $url_array[0];
                    array_shift($url_array);
                }
                $controllerName = $url_array ? ucfirst($url_array[0]) : $controllerName;
                array_shift($url_array);
                $actionName = $url_array ? $url_array[0] : $actionName;
                array_shift($url_array);
                $param = $url_array ? $url_array : [];
            }
        }
        $prefix = TP_NAMESPACE;
        if (!empty($appName)) {
            self::set('app', $appName);
            $appConf = $this->apps[$appName];
            $prefix = isset($appConf['namespace']) ? $appConf['namespace'] : '';
            if (isset($appConf['path'])) define('SUB_APP_PATH', $appConf['path']);
        }
        if (!empty($prefix)) {
            $controllerPrefix = $prefix . '\\Controller\\';
        } else {
            $controllerPrefix = '';
        }
        $controller = $controllerPrefix . $controllerName . 'Controller';
        if (!class_exists($controller)) {
            if (!class_exists($controllerPrefix . 'OtherController')) {
                View::error(['ret' => '-2', 'status' => 'mod does not exist', 'tp_error_msg' => Language::get('mod_not_exists', ['mod' => $controller])], $this->mode);
            } else {
                $controller = $controllerPrefix . 'OtherController';
            }
        }
        $request_method = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';
        $actionFunc = 'ac_' . $actionName . '_' . $request_method;
        if (method_exists($controller, $actionFunc)) {
            $dispatch = new $controller($controllerName, $actionName, $this->mode);
            $this->callAction($controller, $dispatch, $actionFunc, $param);
        } elseif (method_exists($controller, 'ac_' . $actionName)) {
            $dispatch = new $controller($controllerName, $actionName, $this->mode);
            $this->callAction($controller, $dispatch, 'ac_' . $actionName, $param);
        } elseif (method_exists($controller, 'other')) {
            $dispatch = new $controller($controllerName, $actionName, $this->mode);
            $this->callAction($controller, $dispatch, 'other', $param);
        } else {
            View::error(['ret' => '-3', 'status' => 'action does not exist', 'tp_error_msg' => Language::get('action_not_exists', ['action' => $actionName])], $this->mode);
        }
    }

    private function callAction($controller, $dispatch, $action, $pathParam = [])
    {
        try {
            $class = new ReflectionClass($controller);
            $method = $class->getMethod($action);
            $pathValue = [];
            $assignValue = [];
            if ($docComment = $method->getDocComment()) {
                list($flag, $pathValue, $assignValue) = $this->processAnnotation($docComment, $pathParam);
                if ($flag == Filter::STOP) return;
            }
            call_user_func_array([$dispatch, 'assignAll'], [$assignValue]);
            if ($method->getNumberOfParameters() > 0) {
                $params = $method->getParameters();
                $value = [];
                foreach ($params as $param) {
                    $type = '' . $param->getType();
                    $name = '' . $param->getName();
                    if ($param->isOptional()) {
                        $defVal = $param->getDefaultValue();
                    } else {
                        $defVal = '';
                    }
                    if (!empty($type)) {
                        if ($type == 'array') {
                            $value[] = $pathParam;
                        } elseif ($type == 'string') {
                            $p = isset($pathValue[$name]) ? $pathValue[$name] : $defVal;
                            $p = isset($_REQUEST[$name]) ? $_REQUEST[$name] : $p;
                            $value[] = $p;
                        } elseif ($type == 'int') {
                            $p = isset($pathValue[$name]) ? intval($pathValue[$name]) : $defVal;
                            $p = isset($_REQUEST[$name]) ? intval($_REQUEST[$name]) : $p;
                            $value[] = $p;
                        } elseif ($type == 'float') {
                            $p = isset($pathValue[$name]) ? floatval($pathValue[$name]) : $defVal;
                            $p = isset($_REQUEST[$name]) ? floatval($_REQUEST[$name]) : $p;
                            $value[] = $p;
                        } else {
                            if (!isset($this->container[$type])) {
                                $this->container[$type] = new $type();
                            }
                            $value[] = $this->container[$type];
                        }
                    } else {
                        $p = isset($pathValue[$name]) ? $pathValue[$name] : $defVal;
                        $p = isset($_REQUEST[$name]) ? $_REQUEST[$name] : $p;
                        $value[] = $p;
                    }
                }
                call_user_func_array([$dispatch, $action], $value);
            } else {
                call_user_func_array([$dispatch, $action], [$pathParam]);
            }
        } catch (ReflectionException $e) {
            call_user_func_array([$dispatch, $action], [$pathParam]);
        }
    }

    private function processAnnotation($docComment, $pathParam = [])
    {
        /**
         * @var $filter Filter
         */
        $pattern = "#(@[a-zA-Z]+\s*[a-zA-Z0-9, ()_].*)#";
        $pathValue = [];
        $assignValue = [];
        if (preg_match_all($pattern, $docComment, $matches, PREG_PATTERN_ORDER)) {
            foreach ($matches[1] as $decorate) {
                if (strpos($decorate, '@filter') === 0) {
                    $filterInfo = explode(' ', trim($decorate));
                    array_filter($filterInfo);
                    array_shift($filterInfo);
                    $filterName = trim(array_shift($filterInfo));
                    $filterName = self::getClassName($filterName, 'filter', TP_NAMESPACE);
                    $filter = new $filterName($this->mode);
                    $result = $filter->doFilter($filterInfo);
                    if ($result == Filter::STOP) {
                        return [Filter::STOP];
                    }
                    $assignValue = array_merge($assignValue, $filter->getVariable());
                } elseif (strpos($decorate, '@param') === 0) {
                    $patName = '/\$([\w]+)\s*/';
                    $patPath = '/path\(([0-9])(,(.*))?\)/';
                    if (preg_match($patName, $decorate, $matName)) {
                        if (preg_match($patPath, $decorate, $matPath)) {
                            if (isset($pathParam[intval($matPath[1])])) {
                                $pathValue[trim($matName[1])] = $pathParam[intval($matPath[1])];
                            } else {
                                if (isset($matPath[3])) {
                                    $pathValue[trim($matName[1])] = $matPath[3];
                                }
                            }
                        }
                    }
                }
            }
            return [Filter::NEXT, $pathValue, $assignValue];
        }
        return [Filter::NEXT];
    }

    public function get($key)
    {
        return isset($this->variable[$key]) ? $this->variable[$key] : null;
    }

    public function set($key, $value)
    {
        $this->variable[$key] = $value;
    }

    public static function app(): BunnyPHP
    {
        return self::$app;
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

    private function stripSlashesDeep($value)
    {
        $value = is_array($value) ? array_map([$this, 'stripSlashesDeep'], $value) : stripslashes($value);
        return $value;
    }

    private function loadConfig()
    {
        self::$config = Config::load('config');
        define('TP_SITE_NAME', self::$config->get('site_name', 'BunnyPHP'));
        define('TP_SITE_URL', self::$config->get('site_url', 'localhost'));
        define('TP_SITE_REWRITE', self::$config->get('site_rewrite', true));

        if (!defined('TP_NAMESPACE')) define('TP_NAMESPACE', self::$config->get('namespace', ''));

        if (self::$config->has('db')) {
            define('DB_TYPE', self::$config->get('db.type', 'mysql'));
            define('DB_HOST', self::$config->get('db.host', 'localhost'));
            define('DB_PORT', self::$config->get('db.port', '3306'));
            define('DB_NAME', self::$config->get('db.database'));
            define('DB_USER', self::$config->get('db.username'));
            define('DB_PASS', self::$config->get('db.password'));
            define('DB_PREFIX', self::$config->get('db.prefix'));
        }

        if (self::$config->has('apps')) {
            $this->apps = self::$config->get('apps', []);
        }
        BunnyPHP::$request = new Request();
    }


    private static function loadClass($class)
    {
        list($shortName, $type, $prefix) = self::getClassNameInfo($class);
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

    private static function getClassType($class)
    {
        $i = strlen($class) - 1;
        while ($i >= 0 && ($class[$i] < 'A' || $class[$i] > 'Z')) {
            $i--;
        }
        return substr($class, $i);
    }


    public static function getClassName($class, $type = '', $base = TP_NAMESPACE)
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

    private static function getClassNameInfo($class)
    {
        $tmp = explode('\\', $class);
        $shortName = array_pop($tmp);
        $type = array_pop($tmp);
        $prefix = implode('\\', $tmp);
        return [$shortName, $type, $prefix];
    }
}
