<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/7/28
 * Time: 17:42
 */

class BunnyPHP
{
    const MODE_NORMAL = 0;
    const MODE_API = 1;

    protected $config = [];
    protected $mode = BunnyPHP::MODE_NORMAL;

    private static $database;
    private static $storage;

    public function __construct($config, $m = BunnyPHP::MODE_NORMAL)
    {
        $this->config = $config;
        $this->mode = $m;
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

    public function route()
    {
        $controllerName = isset($_GET['mod']) ? ucfirst($_GET['mod']) : $this->config['controller'];
        $actionName = isset($_GET['action']) ? $_GET['action'] : $this->config['action'];
        $param = array();

        $url = $_SERVER['REQUEST_URI'];
        $position = strpos($url, '?');
        $url = ($position === false) ? $url : substr($url, 0, $position);
        $url = trim($url, '/');

        if ($url && strtolower($url) != "index.php" && $this->mode == BunnyPHP::MODE_NORMAL) {
            $urlArray = explode('/', $url);
            $urlArray = array_filter($urlArray);
            if (strtolower($urlArray[0]) == "api") {
                array_shift($urlArray);
                $this->mode = BunnyPHP::MODE_API;
            }
            $controllerName = ucfirst($urlArray[0]);
            array_shift($urlArray);
            $actionName = $urlArray ? $urlArray[0] : $actionName;
            array_shift($urlArray);
            $param = $urlArray ? $urlArray : array();
        } elseif ($this->mode == BunnyPHP::MODE_API) {
            $param = array();
        }

        $controller = $controllerName . 'Controller';
        if (!class_exists($controller)) {
            exit('<h1>' . $controller . ' Not Found</h1>');
        }

        $actionFunc = 'ac_' . $actionName . '_' . strtolower($_SERVER['REQUEST_METHOD']);
        if (method_exists($controller, $actionFunc)) {
            $dispatch = new $controller($controllerName, $actionName, $this->mode);
            call_user_func_array(array($dispatch, $actionFunc), $param);
        } elseif (method_exists($controller, 'ac_' . $actionName)) {
            $dispatch = new $controller($controllerName, $actionName, $this->mode);
            call_user_func_array(array($dispatch, 'ac_' . $actionName), $param);
        } else {
            exit('<h1>Action ' . $actionName . ' Not Exist</h1>');
        }
    }

    public static function getStorage(): Storage
    {
        return self::$storage;
    }

    public function setReporting()
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

    public function stripSlashesDeep($value)
    {
        $value = is_array($value) ? array_map(array($this, 'stripSlashesDeep'), $value) : stripslashes($value);
        return $value;
    }

    public function removeMagicQuotes()
    {
        if (get_magic_quotes_gpc()) {
            $_GET = isset($_GET) ? $this->stripSlashesDeep($_GET) : '';
            $_POST = isset($_POST) ? $this->stripSlashesDeep($_POST) : '';
            $_COOKIE = isset($_COOKIE) ? $this->stripSlashesDeep($_COOKIE) : '';
            $_SESSION = isset($_SESSION) ? $this->stripSlashesDeep($_SESSION) : '';
        }
    }

    public function unregisterGlobals()
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

    public function loadConfig()
    {
        if (isset($this->config['site_name']))
            define("TP_SITE_NAME", $this->config['site_name']);
        if (isset($this->config['site_url']))
            define("TP_SITE_URL", $this->config['site_url']);

        if (isset($this->config['db'])) {
            define('DB_HOST', $this->config['db']['host']);
            define('DB_NAME', $this->config['db']['database']);
            define('DB_USER', $this->config['db']['username']);
            define('DB_PASS', $this->config['db']['password']);
        }

        if (isset($this->config['storage'])) {
            $storageName = $this->config['storage']['name'] . 'Storage';
            BunnyPHP::$storage = new $storageName($this->config['storage']);
        }
    }

    public static function loadClass($class)
    {
        $frameworks = __DIR__ . '/' . $class . '.php';
        $controllers = APP_PATH . 'app/controller/' . $class . '.php';
        $models = APP_PATH . 'app/model/' . $class . '.php';
        $services = APP_PATH . 'app/service/' . $class . '.php';
        $storages = APP_PATH . 'app/storage/' . $class . '.php';
        if (file_exists($frameworks)) {
            include $frameworks;
        } elseif (file_exists($controllers)) {
            include $controllers;
        } elseif (file_exists($models)) {
            include $models;
        } elseif (file_exists($services)) {
            include $services;
        } elseif (file_exists($storages)) {
            include $storages;
        }
    }
}