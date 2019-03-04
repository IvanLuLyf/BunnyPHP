<?php
/**
 * Created by PhpStorm.
 * User: IvanLu
 * Date: 2018/10/31
 * Time: 14:50
 */

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

    public static function render($view, $context = [])
    {
        header("Content-Type: text/html; charset=UTF-8");
        extract($context);
        $cacheDir = APP_PATH . 'cache/template/';
        if (file_exists($cacheDir . $view)) {
            include $cacheDir . $view;
        } elseif (file_exists(APP_PATH . "template/{$view}")) {
            (new self($view))->compile();
            include $cacheDir . $view;
        } else {
            View::error(['ret' => '-3', 'status' => 'template not exists', 'tp_error_msg' => "模板${view}不存在"]);
        }
    }

    public function compile($output = '')
    {
        if ($output == '') {
            $cacheDir = APP_PATH . 'cache/template/';
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0555, true);
            }
            $output = APP_PATH . $cacheDir . $this->template;
        }
        $this->parse_var();
        $this->parse_if();
        $this->parse_for();
        $this->parse_url();
        file_put_contents($output, $this->content);
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

    private function parse_var()
    {
        $pattern = '/\{\{\s*([\w]+)\s*\}\}/';
        if (preg_match($pattern, $this->content)) {
            $this->content = preg_replace($pattern, "<?=\$$1?>", $this->content);
        }
        $pattern = '/\{\{\s*([\w]+).([\w]+)\s*\}\}/';
        if (preg_match($pattern, $this->content)) {
            $this->content = preg_replace($pattern, "<?=\$$1['$2']?>", $this->content);
        }
    }

    private function parse_if()
    {
        $_patternIf = '/\{%\s?if\s+(.*)\s?%\}/';
        $_patternEnd = '/\{%\s?endif\s?%\}/';
        $_patternElse = '/\{%\s?else\s?%\}/';
        if (preg_match($_patternIf, $this->content)) {
            if (preg_match($_patternEnd, $this->content)) {
                $this->content = preg_replace($_patternIf, "<?php if($1):?>", $this->content);
                $this->content = preg_replace($_patternEnd, "<?php endif; ?>", $this->content, 1);
                if (preg_match($_patternElse, $this->content)) {
                    $this->content = preg_replace($_patternElse, "<?php else: ?>", $this->content);
                }
            } else {
                View::error([]);
            }
        }
    }

    private function parse_for()
    {
        $_patternFor = '/\{%\s?for\s+(\w+)\s+in\s+(\w+)\s?%\}/';
        $_patternForKV = '/\{%\s?for\s+(\w+)\s?,\s?(\w+)\s+in\s+(\w+)\s?%\}/';
        $_patternEnd = '/\{%\s?endfor\s?%\}/';
        if (preg_match($_patternFor, $this->content)) {
            if (preg_match($_patternEnd, $this->content)) {
                $this->content = preg_replace($_patternFor, "<?php foreach(\$$2 as \$$1):?>", $this->content);
                $this->content = preg_replace($_patternEnd, "<?php endforeach; ?>", $this->content, 1);
            } else {
                View::error([]);
            }
        }
        if (preg_match($_patternForKV, $this->content)) {
            if (preg_match($_patternEnd, $this->content)) {
                $this->content = preg_replace($_patternForKV, "<?php foreach(\$$3 as \$$1=>\$$2):?>", $this->content);
                $this->content = preg_replace($_patternEnd, "<?php endforeach; ?>", $this->content, 1);
            } else {
                View::error([]);
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
}