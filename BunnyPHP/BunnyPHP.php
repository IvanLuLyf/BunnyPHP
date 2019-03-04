<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/28
 * Time: 17:42
 */

class BunnyPHP
{
    const BUNNY_VERSION = '1.5.0';
    const MODE_NORMAL = 0;
    const MODE_API = 1;
    const MODE_AJAX = 2;

    protected $config;
    protected $mode = BunnyPHP::MODE_NORMAL;

    private static $app;
    private static $storage;
    private static $cache;

    private $variable = [];

    public function __construct($m = BunnyPHP::MODE_NORMAL)
    {
        $this->mode = $m;
        BunnyPHP::$app = $this;
    }

    public function run()
    {
        spl_autoload_register(array($this, 'loadClass'));
        $this->setReporting();
        $this->removeMagicQuotes();
        $this->unregisterGlobals();
        $this->loadConfig();
        $this->route();
    }

    private function route()
    {
        $controllerName = isset($_GET['mod']) ? ucfirst($_GET['mod']) : $this->config->get('controller', 'Index');
        $actionName = isset($_GET['action']) ? $_GET['action'] : 'index';
        $param = [];

        $request_url = $_SERVER['REQUEST_URI'];
        $position = strpos($request_url, '?');
        $request_url = ($position === false) ? $request_url : substr($request_url, 0, $position);
        $request_url = trim($request_url, '/');

        if ($request_url && strtolower($request_url) != "index.php" && $this->mode == BunnyPHP::MODE_NORMAL) {
            $url_array = explode('/', $request_url);
            $url_array = array_filter($url_array);
            if (strtolower($url_array[0]) == "api") {
                array_shift($url_array);
                $this->mode = BunnyPHP::MODE_API;
            } elseif (strtolower($url_array[0]) == "ajax") {
                array_shift($url_array);
                $this->mode = BunnyPHP::MODE_AJAX;
            }
            $controllerName = ucfirst($url_array[0]);
            array_shift($url_array);
            $actionName = $url_array ? $url_array[0] : $actionName;
            array_shift($url_array);
            $param = $url_array ? $url_array : [];
        } elseif ($this->mode == BunnyPHP::MODE_API) {
            $param = [];
        }

        $controller = $controllerName . 'Controller';
        if (!class_exists($controller)) {
            if (!class_exists('OtherController')) {
                View::error(['ret' => '-1', 'status' => 'mod not exists', 'tp_error_msg' => "模块{$controller}不存在"], $this->mode);
            } else {
                $controller = 'OtherController';
            }
        }

        $actionFunc = 'ac_' . $actionName . '_' . strtolower($_SERVER['REQUEST_METHOD']);
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
            View::error(['ret' => '-2', 'status' => 'action not exists', 'tp_error_msg' => "Action {$actionName}不存在"], $this->mode);
        }
    }

    private function callAction($controller, $dispatch, $action, $pathParam = [])
    {
        try {
            $class = new ReflectionClass($controller);
            $method = $class->getMethod($action);
            if ($docComment = $method->getDocComment()) {
                $pattern = "#(@[a-zA-Z]+\s*[a-zA-Z0-9, ()_].*)#";
                if (preg_match_all($pattern, $docComment, $matches, PREG_PATTERN_ORDER)) {
                    foreach ($matches[1] as $decorate) {
                        if (strpos($decorate, '@filter') === 0) {
                            $filters = explode(' ', trim($decorate));
                            array_filter($filters);
                            array_shift($filters);
                            $filter = trim(ucfirst(array_shift($filters))) . 'Filter';
                            $result = (new $filter($this->mode))->doFilter($filters);
                            if ($result == Filter::STOP) {
                                return;
                            }
                        }
                    }
                }
            }
            if ($method->getNumberOfParameters() > 0) {
                $params = $method->getParameters();
                $value = [];
                foreach ($params as $param) {
                    $type = '' . $param->getType();
                    if ($type != '') {
                        if ($type == 'array') {
                            $value[] = $pathParam;
                        } elseif ($type == 'string') {
                            $value[] = isset($_REQUEST[$param->getName()]) ? $_REQUEST[$param->getName()] : '';
                        } else {
                            $value[] = new $type();
                        }
                    } else {
                        $value[] = isset($_REQUEST[$param->getName()]) ? $_REQUEST[$param->getName()] : '';
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
        return self::$storage;
    }

    public static function getCache(): Cache
    {
        return self::$cache;
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
        $value = is_array($value) ? array_map(array($this, 'stripSlashesDeep'), $value) : stripslashes($value);
        return $value;
    }

    private function removeMagicQuotes()
    {
        if (get_magic_quotes_gpc()) {
            $_GET = isset($_GET) ? $this->stripSlashesDeep($_GET) : '';
            $_POST = isset($_POST) ? $this->stripSlashesDeep($_POST) : '';
            $_COOKIE = isset($_COOKIE) ? $this->stripSlashesDeep($_COOKIE) : '';
            $_SESSION = isset($_SESSION) ? $this->stripSlashesDeep($_SESSION) : '';
        }
    }

    private function unregisterGlobals()
    {
        if (ini_get('register_globals')) {
            $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
            foreach ($array as $value) {
                foreach ($GLOBALS[$value] as $key => $var) {
                    if ($var === $GLOBALS[$key]) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }

    private function loadConfig()
    {
        $this->config = Config::load('config');
        define("TP_SITE_NAME", $this->config->get('site_name', 'BunnyPHP'));
        define("TP_SITE_URL", $this->config->get('site_url', 'localhost'));
        define("TP_SITE_REWRITE", $this->config->get('site_rewrite', true));

        if ($this->config->has('db')) {
            define('DB_TYPE', $this->config->get(['db', 'type'], 'mysql'));
            define('DB_HOST', $this->config->get(['db', 'host'], 'localhost'));
            define('DB_PORT', $this->config->get(['db', 'port'], '3306'));
            define('DB_NAME', $this->config->get(['db', 'database']));
            define('DB_USER', $this->config->get(['db', 'username']));
            define('DB_PASS', $this->config->get(['db', 'password']));
            define('DB_PREFIX', $this->config->get(['db', 'prefix']));
        }

        $storageName = 'FileStorage';
        if ($this->config->has('storage')) {
            $storageName = ($this->config->get(['storage', 'name'], 'File')) . 'Storage';
        }
        BunnyPHP::$storage = new $storageName($this->config->get('storage', []));

        $cacheName = 'FileCache';
        if ($this->config->has('cache')) {
            $cacheName = ($this->config->get(['cache', 'name'], 'File')) . 'Cache';
        }
        BunnyPHP::$cache = new $cacheName($this->config->get('cache', []));
    }

    private static function loadClass($class)
    {
        $frameworkFile = __DIR__ . '/' . $class . '.php';
        $controllerFile = APP_PATH . 'app/controller/' . $class . '.php';
        $modelFile = APP_PATH . 'app/model/' . $class . '.php';
        $serviceFile = APP_PATH . 'app/service/' . $class . '.php';
        $storageFile = APP_PATH . 'app/storage/' . $class . '.php';
        if (file_exists($frameworkFile)) {
            include $frameworkFile;
        } elseif (file_exists($controllerFile)) {
            include $controllerFile;
        } elseif (file_exists($modelFile)) {
            include $modelFile;
        } elseif (file_exists($serviceFile)) {
            include $serviceFile;
        } elseif (file_exists($storageFile)) {
            include $storageFile;
        }
    }
}