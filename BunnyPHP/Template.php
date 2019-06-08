<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/10/31
 * Time: 14:50
 */

namespace BunnyPHP;

class Template
{
    protected $template;
    protected $content;

    public function __construct($template)
    {
        if (file_exists(APP_PATH . "template/{$template}")) {
            $this->template = $template;
            $this->content = file_get_contents(APP_PATH . "template/{$template}");
        }
    }

    public static function render($view, $context = [], $mode = BunnyPHP::MODE_NORMAL, $code = 200)
    {
        if ($code !== 200) {
            http_send_status($code);
        }
        if ($mode === BunnyPHP::MODE_API or $mode === BunnyPHP::MODE_AJAX) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode($context, JSON_NUMERIC_CHECK);
        } else {
            header("Content-Type: text/html; charset=UTF-8");
            extract($context);
            $cacheDir = APP_PATH . 'cache/template/';
            if (file_exists($cacheDir . $view)) {
                if (filemtime(APP_PATH . "template/{$view}") > filemtime($cacheDir . $view)) {
                    (new self($view))->compile();
                }
                include $cacheDir . $view;
            } elseif (file_exists(APP_PATH . "template/{$view}")) {
                (new self($view))->compile();
                include $cacheDir . $view;
            } else {
                View::error(['ret' => '-4', 'status' => 'template does not exist', 'tp_error_msg' => "模板${view}不存在"]);
            }
        }
    }

    public function compile($output = '')
    {
        $cacheFile = $cacheDir = APP_PATH . 'cache/template/' . $this->template;
        $output = empty($output) ? $cacheFile : $output;
        $cacheDir = dirname($output);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        file_put_contents($output, $this->parse()->content);
    }

    private function parse()
    {
        $this->parse_var();
        $this->parse_if();
        $this->parse_for();
        $this->parse_url();
        $this->parse_include();
        return $this;
    }

    public static function process($template, $context = [])
    {
        if (file_exists(APP_PATH . "template/{$template}")) {
            $content = file_get_contents(APP_PATH . "template/{$template}");
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
            return $content;
        } else {
            return null;
        }
    }

    private function var_name($word)
    {
        $word_arr = explode('.', trim($word));
        $var_name = '$' . array_shift($word_arr);
        if ($word_arr) {
            $var_name .= '[\'' . join('\'][\'', $word_arr) . '\']';
        }
        return $var_name;
    }

    private function parse_var()
    {
        $pattern = '/\{\{\s*(.*)\s*\}\}/';
        if (preg_match_all($pattern, $this->content, $match)) {
            foreach ($match[1] as $index => $word) {
                $this->content = str_replace($match[0][$index], '<?=' . $this->var_name($word) . '?>', $this->content);
            }
        }
    }

    private function parse_if()
    {
        $_patternIf = '/\{%\s?if\s+(.*)\s?%\}/';
        $_patternEnd = '/\{%\s?endif\s?%\}/';
        $_patternElse = '/\{%\s?else\s?%\}/';
        if (preg_match_all($_patternIf, $this->content, $match)) {
            foreach ($match[0] as $exp) {
                if (preg_match($_patternEnd, $this->content)) {
                    $this->content = preg_replace($_patternIf, "<?php if($1):?>", $this->content, 1);
                    $this->content = preg_replace($_patternEnd, "<?php endif; ?>", $this->content, 1);
                    if (preg_match($_patternElse, $this->content)) {
                        $this->content = preg_replace($_patternElse, "<?php else: ?>", $this->content, 1);
                    }
                } else {
                    View::error(['ret' => -5, 'status' => 'template rendering error', 'tp_error_msg' => $exp . '没有结束标签']);
                }
            }
        }
    }

    private function parse_for()
    {
        $_patternFor = '/\{%\s?for\s+(\w+)\s+in\s+(\w+)\s?%\}/';
        $_patternForKV = '/\{%\s?for\s+(\w+)\s?,\s?(\w+)\s+in\s+(\w+)\s?%\}/';
        $_patternEnd = '/\{%\s?endfor\s?%\}/';
        if (preg_match_all($_patternFor, $this->content, $match)) {
            foreach ($match[0] as $i => $exp) {
                if (preg_match($_patternEnd, $this->content)) {
                    $this->content = str_replace($exp, '<?php foreach($' . $match[2][$i] . ' as ' . $this->var_name($match[1][$i]) . '):?>', $this->content);
                    $this->content = preg_replace($_patternEnd, "<?php endforeach; ?>", $this->content, 1);
                } else {
                    View::error(['ret' => -5, 'status' => 'template rendering error', 'tp_error_msg' => $exp . '没有结束标签']);
                }
            }
        }
        if (preg_match_all($_patternForKV, $this->content, $match)) {
            foreach ($match[0] as $i => $exp) {
                if (preg_match($_patternEnd, $this->content)) {
                    $this->content = str_replace($exp, '<?php foreach($' . $match[3][$i] . ' as ' . $this->var_name($match[1][$i]) . '=>' . $this->var_name($match[2][$i]) . '):?>', $this->content);
                    $this->content = preg_replace($_patternEnd, "<?php endforeach; ?>", $this->content, 1);
                } else {
                    View::error(['ret' => -5, 'status' => 'template rendering error', 'tp_error_msg' => $exp . '没有结束标签']);
                }
            }
        }
    }

    private function parse_url()
    {
        $pattern = '/\{u\s+(.*)\s+\}/';
        if (preg_match($pattern, $this->content)) {
            $this->content = preg_replace($pattern, "<?=View::get_url($1)?>", $this->content);
        }
    }

    private function parse_include()
    {
        $_patternInclude = '/\{%\s?include\s+\'(.*)\'\s?%\}/';
        if (preg_match_all($_patternInclude, $this->content, $match)) {
            foreach ($match[1] as $index => $file) {
                $file_content = (new self($file))->parse()->content;
                $this->content = str_replace($match[0][$index], $file_content, $this->content);
            }
        }
    }
}