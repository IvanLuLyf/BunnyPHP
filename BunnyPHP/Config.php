<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/9/28
 * Time: 15:21
 */

class Config
{
    const MODE_CONST = 0;
    const MODE_ARRAY = 1;
    const MODE_JSON = 2;
    const MODE_SERIAL = 3;

    private $configs = [];

    private function __construct($c = [])
    {
        $this->configs = $c;
    }

    public function all()
    {
        return $this->configs;
    }

    public function has($key)
    {
        if (is_array($key)) {
            return isset($this->configs[$key[0]][$key[1]]);
        } else {
            return isset($this->configs[$key]);
        }
    }

    public function get($key, $defaultVal = '')
    {
        if (is_array($key)) {
            return isset($this->configs[$key[0]][$key[1]]) ? $this->configs[$key[0]][$key[1]] : $defaultVal;
        } else {
            return isset($this->configs[$key]) ? $this->configs[$key] : $defaultVal;
        }
    }

    public static function check($name, $mode = self::MODE_ARRAY, $basePath = APP_PATH . "config/")
    {
        if ($mode == self::MODE_CONST || $mode == self::MODE_ARRAY)
            return file_exists("{$basePath}{$name}.php");
        elseif ($mode == self::MODE_JSON)
            return file_exists("{$basePath}{$name}.json");
        elseif ($mode == self::MODE_SERIAL)
            return file_exists("{$basePath}{$name}.serial");
        else
            return false;
    }

    public static function checkLock($name, $basePath = APP_PATH . "config/")
    {
        return file_exists("{$basePath}{$name}.lock");
    }

    public static function load($name, $basePath = APP_PATH . "config/")
    {
        if (file_exists("{$basePath}{$name}.php")) {
            return new self(require "{$basePath}{$name}.php");
        } elseif (file_exists("{$basePath}{$name}.json")) {
            $configJSON = file_get_contents("{$basePath}{$name}.json");
            return new self(json_decode($configJSON, true));
        } elseif (file_exists("{$basePath}{$name}.serial")) {
            $configSerial = file_get_contents("{$basePath}{$name}.serial");
            return new self(unserialize($configSerial));
        } else {
            return new self();
        }
    }

    public static function make($configs = [], $type = self::MODE_ARRAY)
    {
        $config_text = "<?php\r\n";
        if ($type == self::MODE_CONST) {
            $config_text .= self::make_const_config($configs, '');
        } elseif ($type == self::MODE_JSON) {
            $config_text = json_encode($configs);
        } elseif ($type == self::MODE_SERIAL) {
            $config_text = serialize($configs);
        } else {
            $config_text .= "return " . var_export($configs, true) . ";\r\n";
        }
        return $config_text;
    }

    private static function make_const_config($configs = [], $nameSpace = '')
    {
        $config_text = '';
        foreach ($configs as $k => $v) {
            if (is_array($v)) {
                $config_text .= self::make_const_config($v, $k . '_');
            } elseif (is_int($v)) {
                $config_text .= "define('" . strtoupper($nameSpace . $k) . "',$v);\r\n";
            } else {
                $config_text .= "define('" . strtoupper($nameSpace . $k) . "','" . addslashes($v) . "');\r\n";
            }
        }
        return $config_text;
    }
}