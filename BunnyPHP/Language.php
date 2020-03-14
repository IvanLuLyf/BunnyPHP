<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2019/11/29
 * Time: 13:59
 */

namespace BunnyPHP;


class Language implements \ArrayAccess
{
    public $lang;
    protected $translation;
    private static $instance;

    public function loadLanguage($lang, $basePath)
    {
        if (!$lang) {
            $lang = strtolower(trim(explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'])[0]));
        }
        if (!$basePath) {
            $basePath = APP_PATH . 'lang/';
        }
        $this->lang = $lang;
        $shortLang = explode('-', $lang)[0];
        if (file_exists(BUNNY_PATH . "/lang/{$shortLang}.json")) {
            $json = file_get_contents(BUNNY_PATH . "/lang/{$shortLang}.json");
        } else if (file_exists(BUNNY_PATH . "/lang/{$lang}.json")) {
            $json = file_get_contents(BUNNY_PATH . "/lang/{$lang}.json");
        } else {
            $json = file_get_contents(BUNNY_PATH . '/lang/en.json');
        }
        $sysLang = json_decode($json, true);
        $json = '{}';
        if (file_exists("{$basePath}{$shortLang}.json")) {
            $json = file_get_contents("{$basePath}{$shortLang}.json");
        } else if (file_exists("{$basePath}{$lang}.json")) {
            $json = file_get_contents("{$basePath}{$lang}.json");
        } else if (file_exists("{$basePath}en.json")) {
            $json = file_get_contents("{$basePath}en.json");
        }
        $tmp = json_decode($json, true);
        $tmp = array_merge($sysLang, $tmp);
        if (is_array($this->translation) && count($this->translation) > 0) {
            $this->translation = array_merge($this->translation, $tmp);
        } else {
            $this->translation = $tmp;
        }
    }

    public function translate($key)
    {
        return isset($this->translation[$key]) ? $this->translation[$key] : '';
    }

    public static function getInstance($lang = null, $basePath = null)
    {
        if (!self::$instance) {
            self::$instance = new Language();
        }
        self::$instance->loadLanguage($lang, $basePath);
        return self::$instance;
    }

    public static function get($key, $context = null, $lang = null, $basePath = null)
    {
        $content = self::getInstance($lang, $basePath)->translate($key);
        if (is_array($context)) {
            $pattern = '/\{\{\s*([\w]+)\s*\}\}/';
            if (preg_match_all($pattern, $content, $match)) {
                $ps = [];
                $rs = [];
                for ($i = 0; $i < count($match[0]); $i++) {
                    $ps[] = '/\{\{\s*' . $match[1][$i] . '\s*\}\}/';
                    $rs[] = $context[$match[1][$i]];
                }
                $content = preg_replace($ps, $rs, $content);
            }
        }
        return $content;
    }

    public function offsetExists($offset)
    {
        return isset($this->translation[$offset]);
    }

    public function offsetGet($offset)
    {
        if (isset($this->translation[$offset])) {
            return $this->translation[$offset];
        } else {
            return $offset;
        }
    }

    public function offsetSet($offset, $value)
    {
        return;
    }

    public function offsetUnset($offset)
    {
        unset($this->translation[$offset]);
    }
}