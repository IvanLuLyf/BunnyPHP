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

    public static function check($name)
    {
        return file_exists(APP_PATH . "config/{$name}.php");
    }

    public static function checkLock($name)
    {
        return file_exists(APP_PATH . "config/{$name}.lock");
    }

    public static function load($name)
    {
        if (file_exists(APP_PATH . "config/{$name}.php")) {
            return new self(require APP_PATH . "config/{$name}.php");
        } else {
            return new self();
        }
    }

    public static function make($configs = [], $type = self::MODE_ARRAY)
    {
        $config_text = "<?php\r\n";
        if ($type == self::MODE_CONST) {
            foreach ($configs as $k => $v) {
                $config_text .= "define(\"{$k}\",\"{$v}\");\r\n";
            }
        } else {
            $config_text .= "return [\r\n";
            foreach ($configs as $k => $v) {
                $config_text .= "\"{$k}\"=>";
                if (is_array($v)) {
                    $config_text .= "\t[\r\n";
                    foreach ($v as $k2 => $v2) {
                        $config_text .= "\t\"{$k2}\"=>\"{$v2}\",\r\n";
                    }
                    $config_text .= "],\r\n";
                } else {
                    $config_text .= "\"{$v}\",\r\n";
                }
            }
            $config_text .= '];';
        }
        return $config_text;
    }
}