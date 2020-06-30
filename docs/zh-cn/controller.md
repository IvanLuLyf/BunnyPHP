# 控制器和路由

所有控制器**必须extend** ```Controller```

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

在控制台输入```php cli [mod] [act]``` ,如果存在```[Mod]Controller```,则请求会由这个类响应.

例如,```php cli message init``` 会由```MessageController```响应.

如果在控制器类中有形如```ac_[act]_cli```的函数,则请求由该函数处理.如果不存在则会寻找函数```ac_[act]```来处理,如果都不存在则报错.

例如,```php cli message init``` 会优先寻找```ac_init_cli```响应.

> Web

在浏览器中,请求```/[mod]/[act]``` 会被```[Mod]Controller```内的函数响应.

特别的,如果请求不包含```[act]```,则```[act]```的值为```index```.

如果控制器类里面有形如```ac_[act]_[method]```的指定请求方法的函数名存在,例如```ac_message_get```, ```ac_message_post```或者```ac_message_put```,则会优先由这些函数处理.

如果以上这些函数不存在则会由```ac_[act]```处理.

如果控制器类里面没有```ac_[act]```之类的函数,但是存在```other```函数,则请求由```other```函数处理,并可以使用```$this->getAction()```来获取```[act]```的内容.

如果都不存在则报错.

> API

API请求以```/api/```开头,形如```/api/[mod]/[act]```.并以JSON格式返回数据.

> AJAX

AJAX```/ajax/```开头,例如```/ajax/[mod]/[act]```.并以JSON格式返回数据.

> 优先级

```ac_[act]_[method]  >  ac_[act]  >  other```

> 依赖注入

在调用控制器的Action函数时,框架会自动注入参数.

例如

```php
public function ac_test(UserModel $userModel,string $name,int $id=1){

}
```

在此样例中$userModel变量会自动获取一个new UserModel()实例.$name会获取```$_REQUEST['name']```的值,如果没有设置```$_REQUEST['name']```且未设置缺省值,则返回```''```.$id会获取```$_REQUEST['id']```的值,如果没有设置则获取缺省值```1```.

特别的,如果函数参数没有指定变量类型,也会以string类型自动获取$_REQUEST的值.

> 变量输出

对于要输出的变量,需要调用```assign($name,$value)```或者```assignAll($dataArray)```.然后调用```render([HTML页面])```,```error()```或者```renderTemplate([HTML模板])```渲染结果页面.

### 注解

控制器的Action函数支持使用注解

> @param注解

如果在@param注解里面有```path(postion)```或者```path(position,default)```.会让参数得到获取Path变量的能力.

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

在请求```/test/test/Bunny/2```中,```$name```变量会获取path(0)的值即```'Bunny'``` ,```$page```变量会获取path(1)的值```2```.

在请求```/test/test/Bunny```中,```$name```变量会获取path(0)的值即```'Bunny'``` ,```$page```变量会获取path(1)的缺省值```1```.

在请求```/test/test```中,```$name```变量会获取path(0)的缺省值即```'Test'``` ,```$page```变量会获取path(1)的缺省值```1```.

特别的如果同时存在变量```$_REQUEST['name']```和path变量的值存在,最终值为```$_REQUEST```的值.

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

会先调用```TestFilter```的```doFilter```函数.如果返回值是```Filter::NEXT```则执行下一个过滤器,在例子中是```HelloFilter```.如果函数返回值是```Filter::STOP```则停止执行剩余Filter和Action函数.
