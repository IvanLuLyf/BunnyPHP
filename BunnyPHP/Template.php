<?php
declare(strict_types=1);

namespace BunnyPHP;
class Template
{
    protected string $template;
    protected $content;

    public function __construct($template)
    {
        if (file_exists(APP_PATH . "template/$template")) {
            $this->template = $template;
            $this->content = file_get_contents(APP_PATH . "template/$template");
        }
    }

    private static function tag($txt): string
    {
        return '<?php ' . $txt . ' ?>';
    }

    public static function escape($value): string
    {
        if ($value === null) return '';
        if (is_bool($value)) return $value ? '1' : '0';
        if (is_scalar($value)) return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function render($view, $context = [], $mode = BunnyPHP::MODE_NORMAL, $code = 200)
    {
        if ($code !== 200) {
            http_response_code($code);
        }
        if ($mode === BunnyPHP::MODE_API or $mode === BunnyPHP::MODE_AJAX) {
            View::json($context);
        } else {
            header('Content-Type: text/html; charset=UTF-8');
            extract($context);
            global $_LANG;
            $_LANG = Language::getInstance();
            $cacheDir = APP_PATH . 'cache/template/';
            if (file_exists($cacheDir . $view)) {
                if (filemtime(APP_PATH . "template/$view") > filemtime($cacheDir . $view)) {
                    (new self($view))->compile();
                }
                include $cacheDir . $view;
            } elseif (file_exists(APP_PATH . "template/$view")) {
                (new self($view))->compile();
                include $cacheDir . $view;
            } else {
                View::error(['ret' => -4, 'status' => 'template does not exist', 'bunny_error' => Language::get('view_not_exists', ['view' => $view])]);
            }
        }
    }

    public function compile($output = '')
    {
        $cacheFile = APP_PATH . 'cache/template/' . $this->template;
        $output = empty($output) ? $cacheFile : $output;
        $cacheDir = dirname($output);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        file_put_contents($output, $this->parse()->content);
    }

    private function parse(): Template
    {
        $this->parse_comment();
        $this->parse_var();
        $this->parse_if();
        $this->parse_for();
        $this->parse_lang();
        $this->parse_url();
        $this->parse_include();
        return $this;
    }

    public static function process($template, $context = [])
    {
        if (file_exists(APP_PATH . "template/$template")) {
            $content = file_get_contents(APP_PATH . "template/$template");
            $pattern = '/{{\s*([\w]+)\s*}}/';
            if (preg_match_all($pattern, $content, $match)) {
                $ps = [];
                $rs = [];
                for ($i = 0; $i < count($match[0]); $i++) {
                    $ps[] = '/\{\{\s*' . $match[1][$i] . '\s*\}\}/';
                    $rs[] = $context[$match[1][$i]];
                }
                $content = preg_replace($ps, $rs, $content);
            }
            return $content;
        } else {
            return null;
        }
    }

    private function var_name($word): string
    {
        $word_arr = explode('.', trim($word));
        $var_name = '$' . array_shift($word_arr);
        if ($word_arr) {
            $var_name .= '[\'' . join('\'][\'', $word_arr) . '\']';
        }
        return $var_name;
    }

    private function compile_expr(string $expr): string
    {
        $expr = trim($expr);
        if ($expr === '') return "''";

        $parts = array_map('trim', explode('|', $expr));
        $base = array_shift($parts);

        if (preg_match('/^(\'[^\']*\'|"[^"]*"|\d+(\.\d+)?|true|false|null)$/i', $base)) {
            $php = $base;
        } else {
            $php = $this->var_name($base);
        }

        foreach ($parts as $filter) {
            if ($filter === '') continue;
            if ($filter === 'safe') {
                continue;
            }
            if ($filter === 'e' || $filter === 'escape') {
                $php = '\\' . __NAMESPACE__ . '\\Template::escape(' . $php . ')';
                continue;
            }
            if ($filter === 'upper') {
                $php = 'strtoupper((string)(' . $php . '))';
                continue;
            }
            if ($filter === 'lower') {
                $php = 'strtolower((string)(' . $php . '))';
                continue;
            }
            if ($filter === 'json') {
                $php = 'json_encode(' . $php . ', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)';
                continue;
            }
            if (preg_match('/^default\\((.*)\\)$/', $filter, $m)) {
                $def = trim($m[1]);
                if ($def === '') $def = "''";
                $php = '((' . $php . ') === null || (' . $php . ') === \'\' ? ' . $def . ' : (' . $php . '))';
                continue;
            }
        }

        return $php;
    }

    private function parse_comment()
    {
        $pattern = '/{#([\\s\\S]*?)#}/';
        $this->content = preg_replace($pattern, '', $this->content);
    }

    private function parse_var()
    {
        $pattern = '/{{\\s*(.*?)\\s*}}/';
        if (!preg_match_all($pattern, $this->content, $match)) return;
        foreach ($match[1] as $index => $word) {
            $phpExpr = $this->compile_expr($word);
            $replacement = '<?=(' . $phpExpr . ')?>';
            $this->content = str_replace($match[0][$index], $replacement, $this->content);
        }
    }

    private function parse_if()
    {
        $_patternIf = '/{%\s?if\s+(.*)\s?%}/';
        $_patternEnd = '/{%\s?endif\s?%}/';
        $_patternElse = '/{%\s?else\s?%}/';
        if (preg_match_all($_patternIf, $this->content, $match)) {
            foreach ($match[0] as $exp) {
                if (preg_match($_patternEnd, $this->content)) {
                    $this->content = preg_replace($_patternIf, self::tag('if($1):'), $this->content, 1);
                    $this->content = preg_replace($_patternEnd, self::tag('endif;'), $this->content, 1);
                    if (preg_match($_patternElse, $this->content)) {
                        $this->content = preg_replace($_patternElse, self::tag('else:'), $this->content, 1);
                    }
                } else {
                    View::error(['ret' => -5, 'status' => 'template rendering error', 'bunny_error' => $exp . '没有结束标签']);
                }
            }
        }
    }

    private function parse_for()
    {
        $_patternFor = '/{%\s?for\s+(\w+)\s+in\s+(\w+)\s?%}/';
        $_patternForKV = '/{%\s?for\s+(\w+)\s?,\s?(\w+)\s+in\s+(\w+)\s?%}/';
        $_patternEnd = '/{%\s?endfor\s?%}/';
        if (preg_match_all($_patternFor, $this->content, $match)) {
            foreach ($match[0] as $i => $exp) {
                if (preg_match($_patternEnd, $this->content)) {
                    $this->content = str_replace($exp, self::tag('foreach($' . $match[2][$i] . ' as ' . $this->var_name($match[1][$i]) . '):'), $this->content);
                    $this->content = preg_replace($_patternEnd, self::tag('endforeach;'), $this->content, 1);
                } else {
                    View::error(['ret' => -5, 'status' => 'template rendering error', 'bunny_error' => $exp . '没有结束标签']);
                }
            }
        }
        if (preg_match_all($_patternForKV, $this->content, $match)) {
            foreach ($match[0] as $i => $exp) {
                if (preg_match($_patternEnd, $this->content)) {
                    $this->content = str_replace($exp, self::tag('foreach($' . $match[3][$i] . ' as ' . $this->var_name($match[1][$i]) . '=>' . $this->var_name($match[2][$i]) . '):'), $this->content);
                    $this->content = preg_replace($_patternEnd, self::tag('endforeach;'), $this->content, 1);
                } else {
                    View::error(['ret' => -5, 'status' => 'template rendering error', 'bunny_error' => $exp . '没有结束标签']);
                }
            }
        }
    }

    private function parse_url()
    {
        $pattern = '/{u\s+(.*)\s+}/';
        if (preg_match($pattern, $this->content)) {
            $this->content = preg_replace($pattern, '<?=BunnyPHP\View::get_url($1)?>', $this->content);
        }
    }

    private function parse_lang()
    {
        $pattern = '/{L\s*([\'"\w+]*)\s*}/';
        if (preg_match($pattern, $this->content)) {
            $this->content = preg_replace($pattern, '<?=$_LANG[$1]?>', $this->content);
        }
    }

    private function parse_include()
    {
        $_patternInclude = '/{%\s?include\s+\'(.*)\'\s?%}/';
        if (preg_match_all($_patternInclude, $this->content, $match)) {
            foreach ($match[1] as $index => $file) {
                $file_content = (new self($file))->parse()->content;
                $this->content = str_replace($match[0][$index], $file_content, $this->content);
            }
        }
    }
}