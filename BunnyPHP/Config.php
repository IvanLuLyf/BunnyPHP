<?php
declare(strict_types=1);

namespace BunnyPHP;
class Config
{
    const MODE_CONST = 0;
    const MODE_ARRAY = 1;
    const MODE_JSON = 2;
    const MODE_SERIAL = 3;

    private $configs;

    private function __construct($c = [])
    {
        $this->configs = $c;
    }

    public function all(): array
    {
        return $this->configs;
    }

    public function has($key): bool
    {
        if (is_string($key) && strpos($key, '.') !== false) {
            $key = array_filter(explode('.', $key));
        }
        if (is_array($key)) {
            $tmp = $this->configs;
            foreach ($key as $k) {
                if (!isset($tmp[$k])) {
                    return false;
                }
                $tmp = $tmp[$k];
            }
            return true;
        } else {
            return isset($this->configs[$key]);
        }
    }

    public function get($key, $defaultVal = '')
    {
        if (is_string($key) && strpos($key, '.') !== false) {
            $key = array_filter(explode('.', $key));
        }
        if (is_array($key)) {
            $tmp = $this->configs;
            foreach ($key as $k) {
                if (!isset($tmp[$k])) {
                    return $defaultVal;
                }
                $tmp = $tmp[$k];
            }
            return $tmp;
        } else {
            return $this->configs[$key] ?? $defaultVal;
        }
    }

    public static function check($name, $mode = self::MODE_ARRAY, $basePath = APP_PATH . 'config/'): bool
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

    public static function checkLock($name, $basePath = APP_PATH . 'config/'): bool
    {
        return file_exists("{$basePath}{$name}.lock");
    }

    public static function load($name, $basePath = APP_PATH . 'config/'): Config
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

    public static function make($configs = [], $type = self::MODE_ARRAY): string
    {
        $config_text = "<?php\r\n";
        if ($type == self::MODE_CONST) {
            $config_text .= self::make_const_config($configs, '');
        } elseif ($type == self::MODE_JSON) {
            $config_text = json_encode($configs);
        } elseif ($type == self::MODE_SERIAL) {
            $config_text = serialize($configs);
        } else {
            $config_text .= 'return ' . var_export($configs, true) . ";\r\n";
        }
        return $config_text;
    }

    private static function make_const_config($configs = [], $nameSpace = ''): string
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