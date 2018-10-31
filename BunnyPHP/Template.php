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
        if (file_exists(APP_PATH . "template/{$template}.tpl.html")) {
            $this->template = $template;
            $this->content = file_get_contents(APP_PATH . "template/{$template}.tpl.html");
        }
    }

    public function compile($output = '')
    {
        if ($output == '') {
            $output = APP_PATH . "template/{$this->template}.html";
        }
        $this->parse_var();
        $this->parse_if();
        file_put_contents($output, $this->content);
    }

    private function parse_var()
    {
        $pattern = '/\{\{\s+([\w]+)\s+\}\}/';
        if (preg_match($pattern, $this->content)) {
            $this->content = preg_replace($pattern, "<?=\$$1?>", $this->content);
        }
        $pattern = '/\{\{\s+([\w]+).([\w]+)\s+\}\}/';
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
                $this->content = preg_replace($_patternEnd, "<?php endif; ?>", $this->content);
                if (preg_match($_patternElse, $this->content)) {
                    $this->content = preg_replace($_patternElse, "<?php else: ?>", $this->content);
                }
            } else {
                View::error([]);
            }
        }
    }
}