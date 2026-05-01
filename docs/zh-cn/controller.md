# 控制器和路由

所有控制器**必须**继承 `BunnyPHP\Controller`。

> 样例

```php
<?php

use BunnyPHP\Controller;

class MessageController extends Controller
{
    public function ac_init_cli()
    {
        MessageModel::create();
        $this->assign('response', 'Table Created')->render();
    }

    public function ac_list(MessageModel $model)
    {
        $messages = $model->fetchAll();
        $this->assign('messages', $messages)->render('list.html');
    }

    public function ac_message_get($id, MessageModel $model)
    {
        $message = $model->getMessage($id);
        $this->assign('message', $message)->render('view.html');
    }

    public function ac_message_post($message, MessageModel $model)
    {
        $id = $model->addMessage($message);
        $this->redirect('test', 'message', ['id' => $id]);
    }
}
```

> Cli

在控制台输入 `php cli [mod] [act]`，如果存在 `[Mod]Controller`，则请求会由这个类处理。

例如,```php cli message init``` 会由```MessageController```响应.

如果在控制器类中有形如 `ac_[act]_cli` 的函数，则优先由该函数处理；如果不存在则会寻找 `ac_[act]`；如果都不存在则报错。

例如,```php cli message init``` 会优先寻找```ac_init_cli```响应.

> Web

在浏览器中，请求 `/<mod>/<act>` 会被 `[Mod]Controller` 内对应的方法处理。

特别地，如果请求不包含 `[act]`，则默认值为 `index`。

如果控制器类里面有形如 `ac_[act]_[method]` 的方法（例如 `ac_message_get`、`ac_message_post`、`ac_message_put`），则优先由这些方法处理。

否则回退到 `ac_[act]`。

如果控制器类里面没有 `ac_[act]`，但存在 `other()`，则请求由 `other()` 处理，并可用 `$this->getAction()` 获取当前 `[act]`。

如果都不存在则报错.

> API

API 请求以 `/api/` 开头，形如 `/api/[mod]/[act]`，并以 JSON 格式返回数据。

> AJAX

AJAX 请求以 `/ajax/` 开头，例如 `/ajax/[mod]/[act]`，并以 JSON 格式返回数据。

> 优先级

`ac_[act]_[method]  >  ac_[act]  >  other`

> 依赖注入

在调用控制器的Action函数时,框架会自动注入参数.

例如

```php
public function ac_test(UserModel $userModel,string $name,int $id=1){

}
```

在此样例中：`$userModel` 会自动解析为对象实例；`$name` 会从 `$_REQUEST['name']` 读取（否则回退 `''`）；`$id`
会从 `$_REQUEST['id']` 读取（否则回退 `1`）。

特别地，如果函数参数没有指定类型，也会以 string 的方式从 `$_REQUEST` 获取。

在 PHP 8+ 环境下，注入器也支持 union / nullable 类型提示。

> 变量输出

对于要输出的变量，调用 `assign($name, $value)` 或 `assignAll($dataArray)`，然后通过 `render([view])` / `error()` 输出即可。

### 注解

控制器的Action函数支持使用注解

> @param注解

如果在 `@param` 注解里面有 `path(position)` 或 `path(position, default)`，参数就能从 URL path 段读取值。

例如:

```php
<?php

use BunnyPHP\Controller;

class TestController extends Controller {
    /**
     * @param $name string path(0,Test)
     * @param $page integer path(1,1)
     */
    public function ac_test($page, $name){
    
    }
}
```

在请求```/test/test/Bunny/2```中,```$name```变量会获取path(0)的值即```'Bunny'``` ,```$page```变量会获取path(1)
的值```2```.

在请求```/test/test/Bunny```中,```$name```变量会获取path(0)的值即```'Bunny'``` ,```$page```变量会获取path(1)
的缺省值```1```.

在请求```/test/test```中,```$name```变量会获取path(0)的缺省值即```'Test'``` ,```$page```变量会获取path(1)
的缺省值```1```.

特别地，如果同时存在 `$_REQUEST['name']` 和 path 变量，最终值以 `$_REQUEST` 为准。

例如,请求```/test/test/Bunny?name=PHP```,最终```$name```获取的值为```'PHP'```.

> @filter注解

如果函数内定义了@filter注解,会先调用对应过滤器的```doFilter```函数,再执行控制器的Action函数.

例如

```php
<?php

use BunnyPHP\Controller;

class TestController extends Controller {
    /**
     * @filter test
     * @filter hello
     */
    public function ac_test(){
    
    }
}
```

会先调用 `TestFilter` 的 `doFilter()`。如果返回 `Filter::NEXT` 则执行下一个过滤器（例子中为 `HelloFilter`
）；如果返回 `Filter::STOP` 则停止执行剩余 Filter 和 Action。

### PHP 8 Attributes（过滤器）

在 PHP 8+ 环境下，也可以用 Attributes 声明过滤器：

```php
use BunnyPHP\Annotation\UseFilter;

#[UseFilter('auth')]
class UserController extends Controller
{
    #[UseFilter('csrf')]
    public function ac_update()
    {
    }
}
```
