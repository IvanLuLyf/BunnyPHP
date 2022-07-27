<?php
declare(strict_types=1);

namespace BunnyPHP;

use ArrayAccess;
use ReturnTypeWillChange;

class Language implements ArrayAccess
{
    public string $lang;
    protected array $translation = [];
    private static ?Language $instance = null;

    public function loadLanguage($lang, $basePath)
    {
        if (!$lang) {
            if (key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
                $lang = strtolower(trim(explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'])[0]));
            } elseif (key_exists('LANG', $_SERVER)) {
                $lang = str_replace('_', '-', strtolower(trim(explode('.', $_SERVER['LANG'])[0])));
            } else {
                $lang = 'en';
            }
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

    public function translate($key): string
    {
        return $this->translation[$key] ?? '';
    }

    public static function getInstance($lang = null, $basePath = null): Language
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

    public function offsetExists($offset): bool
    {
        return isset($this->translation[$offset]);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->translation[$offset] ?? $offset;
    }

    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
    }

    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->translation[$offset]);
    }
}
