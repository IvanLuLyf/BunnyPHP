# Controller and Router

Every Controller **must extend** `BunnyPHP\Controller`.

> Sample

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

Enter `php cli [mod] [act]` in the console. If `[Mod]Controller` exists, the request will be handled by this class.

For example, `php cli message init` will be handled by `MessageController`.

If `ac_[act]_cli` exists, the request will be processed by it. Otherwise it will fallback to `ac_[act]`. If neither
exists, an error will be reported.

For example, `php cli message init` will look for `ac_init_cli` first.

> Web

In the browser, the request `/[mod]/[act]` will be handled by a method in `[Mod]Controller`.

If `[act]` is missing, it defaults to `index`.

If `ac_[act]_[method]` exists (e.g. `ac_message_get`, `ac_message_post`, `ac_message_put`), it has higher priority.

Otherwise it falls back to `ac_[act]`.

If `ac_[act]` does not exist but `other()` exists, the request will be handled by `other()`. You can
use `$this->getAction()` to get the current `[act]`.

If they do not exist, an error will be reported.

> API

API requests start with `/api/`, like `/api/[mod]/[act]`.

They will respond in JSON.

> AJAX

AJAX requests start with `/ajax/`, like `/ajax/[mod]/[act]`.

They will respond in JSON.

> Priority

`ac_[act]_[method]  >  ac_[act]  >  other`

> Dependency Injection

The framework automatically injects parameters when calling the Controller's Action function.

For example:

```php
public function ac_test(UserModel $userModel,string $name,int $id=1){

}
```

In this example, `$userModel` will be resolved as an object instance, `$name` will be read from `$_REQUEST['name']` (or
fallback to `''`), and `$id` will be read from `$_REQUEST['id']` (or fallback to `1`).

If a parameter has no type-hint, it will be read from `$_REQUEST` as a string by default.

On PHP 8+, union / nullable type-hints are supported by the injector.

> Variable output

Use `assign($name, $value)` / `assignAll($dataArray)` and then call `render([view])` / `error()` to output.

### Annotation

Controller actions support DocBlock annotations, and filters also support PHP 8 Attributes.

> @param annotation

If you use `path(position)` or `path(position, default)` in `@param`, the parameter can read from URL path segments.

For example:

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

In the request `/test/test/Bunny/2`, `$name` gets `'Bunny'` and `$page` gets `2`.

In the request `/test/test/Bunny`, `$name` gets `'Bunny'` and `$page` gets default `1`.

In the request `/test/test`, `$name` gets default `'Test'` and `$page` gets default `1`.

If both `$_REQUEST['name']` and a path value exist, the final value uses `$_REQUEST`.

For example `/test/test/Bunny?name=PHP`, `$name` becomes `'PHP'`.

> @filter annotation

If the @filter annotation is defined in the function, the ```doFilter``` function of the corresponding filter is called
first, and then the Controller's Action function is executed.

For example

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

It will call ```TestFilter```'s```doFilter``` function first.If the return value is ```Filter::NEXT``` then execute the
next filter, in the example it is ```HelloFilter```. If the function return value is ```Filter::STOP``` then stop
Execute the remaining Filter and Action functions.

### PHP 8 Attributes (filters)

On PHP 8+, you can also declare filters with Attributes:

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
