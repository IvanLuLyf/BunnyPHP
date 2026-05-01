<?php
declare(strict_types=1);

namespace BunnyPHP;

use ArrayAccess;
use ReturnTypeWillChange;

class Language implements ArrayAccess
{
    public string $lang;
    private static ?Language $instance = null;
    private static ?string $forcedLocale = null;
    private static array $catalog = [];

    public function loadLanguage($lang, $basePath)
    {
        if ($lang) {
            self::$forcedLocale = self::normalizeLocale((string)$lang);
        } else {
            self::$forcedLocale = null;
        }
        $this->lang = self::getLocale();
    }

    public function translate($key): string
    {
        return self::t((string)$key, [], $this->lang);
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
        $loc = $lang ? self::normalizeLocale((string)$lang) : null;
        $params = is_array($context) ? $context : [];
        return self::t((string)$key, $params, $loc);
    }

    public function offsetExists($offset): bool
    {
        $k = (string)$offset;
        $loc = $this->lang ?: self::getLocale();
        return self::has($k, 'app', $loc) || self::has($k, 'bunny', $loc);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->translate((string)$offset);
    }

    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
    }

    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
    }

    public static function getLocale(): string
    {
        if (self::$forcedLocale) return self::$forcedLocale;

        // minimal: query (?lang=) -> cookie -> session -> Accept-Language -> default
        if (!empty($_GET['lang']) && is_string($_GET['lang'])) {
            return self::normalizeLocale($_GET['lang']);
        }
        $c = Request::cookie('bunny_lang');
        if (is_string($c) && $c !== '') return self::normalizeLocale($c);
        $s = Request::session('bunny_lang');
        if (is_string($s) && $s !== '') return self::normalizeLocale($s);
        $h = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        if (is_string($h) && $h !== '') {
            $p = self::parseAcceptLanguage($h);
            if ($p) return $p;
        }
        return 'en';
    }

    public static function t(string $key, array $params = [], ?string $locale = null): string
    {
        $loc = $locale ? self::normalizeLocale($locale) : self::getLocale();
        $value = self::getMessage($key, 'app', $loc);
        if ($value === null) $value = self::getMessage($key, 'bunny', $loc);
        if ($value === null) {
            if (defined('APP_DEBUG') && APP_DEBUG === true) return "__MISSING__:{$loc}:$key";
            return $key;
        }
        if ($params) $value = self::interpolate($value, $params);
        return $value;
    }

    private static function has(string $key, string $domain, string $locale): bool
    {
        return self::getMessage($key, $domain, $locale) !== null;
    }

    private static function getMessage(string $key, string $domain, string $locale): ?string
    {
        $cat = self::loadCatalog($domain, $locale);
        if (!is_array($cat)) return null;
        if (strpos($key, '.') !== false) {
            $path = array_filter(explode('.', $key));
            $tmp = $cat;
            foreach ($path as $p) {
                if (!is_array($tmp) || !array_key_exists($p, $tmp)) return null;
                $tmp = $tmp[$p];
            }
            return is_string($tmp) ? $tmp : null;
        }
        return isset($cat[$key]) && is_string($cat[$key]) ? $cat[$key] : null;
    }

    private static function loadCatalog(string $domain, string $locale): array
    {
        $k = $domain . '|' . $locale;
        if (isset(self::$catalog[$k])) return self::$catalog[$k];

        $sourceFile = self::resolveSourceFile($domain, $locale);
        if (!$sourceFile) {
            self::$catalog[$k] = [];
            return self::$catalog[$k];
        }

        $mtime = file_exists($sourceFile) ? (int)filemtime($sourceFile) : 0;
        $useCache = !(defined('APP_DEBUG') && APP_DEBUG === true);

        if ($useCache) {
            $cacheDir = BunnyPHP::getDir('@cache/i18n');
            $cacheFile = $cacheDir . self::cacheKey($domain, $locale) . '.php';
            if (file_exists($cacheFile)) {
                $cached = include $cacheFile;
                if (is_array($cached) && ($cached['_mtime'] ?? -1) === $mtime) {
                    $data = $cached['_data'] ?? null;
                    self::$catalog[$k] = is_array($data) ? $data : [];
                    return self::$catalog[$k];
                }
            }
        }

        $raw = @file_get_contents($sourceFile);
        $data = [];
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) $data = $decoded;
        }

        if ($useCache) {
            $cacheDir = BunnyPHP::getDir('@cache/i18n');
            $cacheFile = $cacheDir . self::cacheKey($domain, $locale) . '.php';
            @file_put_contents($cacheFile, '<?php return ' . var_export(['_mtime' => $mtime, '_data' => $data], true) . ';');
        }

        self::$catalog[$k] = $data;
        return self::$catalog[$k];
    }

    private static function resolveSourceFile(string $domain, string $locale): ?string
    {
        $short = explode('-', $locale)[0];
        $baseDir = ($domain === 'bunny')
            ? (BUNNY_PATH . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR)
            : (APP_PATH . 'lang' . DIRECTORY_SEPARATOR);

        if ($domain !== 'app' && $domain !== 'bunny') {
            $baseDir = $baseDir . trim($domain, '/\\') . DIRECTORY_SEPARATOR;
        }

        $candidates = [
            $baseDir . $locale . '.json',
            $baseDir . $short . '.json',
            $baseDir . 'en.json',
        ];
        foreach ($candidates as $c) {
            if (file_exists($c)) return $c;
        }
        return null;
    }

    private static function cacheKey(string $domain, string $locale): string
    {
        $domain = preg_replace('/[^a-z0-9_\\-]/i', '_', $domain);
        $locale = preg_replace('/[^a-z0-9_\\-]/i', '_', $locale);
        return strtolower($domain . '_' . $locale);
    }

    private static function interpolate(string $text, array $params): string
    {
        foreach ($params as $k => $v) {
            $val = is_scalar($v) || $v === null ? (string)$v : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $text = str_replace('{' . $k . '}', $val, $text);
        }
        return $text;
    }

    private static function parseAcceptLanguage(string $value): ?string
    {
        $parts = array_map('trim', explode(',', $value));
        if (!$parts) return null;
        $best = null;
        $bestQ = -1.0;
        foreach ($parts as $part) {
            if ($part === '') continue;
            $seg = array_map('trim', explode(';', $part));
            $lang = $seg[0] ?? '';
            if ($lang === '') continue;
            $q = 1.0;
            if (isset($seg[1]) && stripos($seg[1], 'q=') === 0) {
                $q = (float)substr($seg[1], 2);
            }
            if ($q > $bestQ) {
                $bestQ = $q;
                $best = $lang;
            }
        }
        return $best ? self::normalizeLocale($best) : null;
    }

    private static function normalizeLocale(string $locale): string
    {
        $locale = str_replace('_', '-', trim($locale));
        if ($locale === '') return 'en';
        $locale = strtolower($locale);
        $parts = explode('-', $locale);
        if (count($parts) === 1) return $parts[0];
        $parts[1] = strtoupper($parts[1]);
        return $parts[0] . '-' . $parts[1];
    }
}
