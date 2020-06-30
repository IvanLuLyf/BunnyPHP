# Controller and Router

Every Controller **must extend** ```Controller```

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

Enter ```php cli [mod] [act]``` in the console. If there is ```[Mod]Controller```, the request will be handle by this class.

For example, ```php cli message init``` will handle by ```MessageController```.

If there is a function like ```ac_[act]_cli``` in the controller class, the request is processed by this function. If it does not exist, it will look for the function ```ac_[act]``` to handle it. If they do not exist, an error is reported.

For example, ```php cli message init``` will look for the ```ac_init_cli``` response first.

> Web

In the browser, the request ```/[mod]/[act]``` will be responded to by the function in ```[Mod]Controller```.

In particular, if the request does not contain ```[act]```, the value of ```[act]``` is ```index```.

If the function name of the specified request method like ```ac_[act]_[method]``` exists in the controller class, for example, ```ac_message_get```, ```ac_message_post``` or ``` ac_message_put```, the request will be processed first by these functions.

If these functions do not exist, they will be handled by ```ac_[act]```.

If there is no function like ```ac_[act]``` in the controller class, but there has the ```other``` function, the request will be handled by the ```other``` function, and you can use ```$this->getAction()``` to get the contents of ```[act]```.

If they do not exist, an error will be reported.

> API

API requests start with ```/api/```, which is like ```/api/[mod]/[act]```.

It will be displayed in JSON format.

> AJAX

API requests start with ```/ajax/```, which is like ```/ajax/[mod]/[act]```.

It will be displayed in JSON format.

> Priority

```ac_[act]_[method]  >  ac_[act]  >  other```

> Dependency Injection

The framework automatically injects parameters when calling the Controller's Action function.

For Example

```php
public function ac_test(UserModel $userModel,string $name,int $id=1){

}
```

In this example, the $userModel variable will automatically get a ```new UserModel()``` instance. ```$name``` will get the value of ```$_REQUEST['name']```, if ```$_REQUEST['name']``` is not set and the default value is not set, then return ```''```.```$id``` will get the value of ```$_REQUEST['id']```, if not set, get the default value ```1```.

In particular, if the function parameter does not specify a variable type, the value of ```$_REQUEST``` is automatically obtained as a string type.

> Variable output

For the variable to be output, you need to call ```assign($name,$value)``` or ```assignAll($dataArray)```. Then call ```render([HTML page])``` , ```error()``` or ```renderTemplate([HTML template])``` rendering results page.

### Annotation

The Controller's Action function supports the use of annotations.

> @param annotation

If there is ```path(postion)``` or ```path(position,default)```. in the @param annotation, the parameter will get the ability to get the Path variable.

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

In the request ```/test/test/Bunny/2```, the variable ```$name``` will get the value of path(0) ```'Bunny'```, The variable ```$page```  will get the value of path(1) ```2```.

In the request ```/test/test/Bunny```, the variable ```$name``` will get the value of path(0) ```'Bunny'```, The variable ```$page``` will get the default value of path(1) ```1```.

In the request ```/test/test```, the variable ```$name``` will get the default value of path(0) ```'Test'```, The variable ```$page``` will get the default value of path(1) ```1```.

In particular, if there is a variable ```$_REQUEST['name']``` and the value of the path variable exists, the final value is the value in ```$_REQUEST```.

For example, request ```/test/test/Bunny?name=PHP```, and the final ```$name``` gets the value ```'PHP'```.

> @filter annotation

If the @filter annotation is defined in the function, the ```doFilter``` function of the corresponding filter is called first, and then the Controller's Action function is executed.

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

It will call ```TestFilter```'s```doFilter``` function first.If the return value is ```Filter::NEXT``` then execute the next filter, in the example it is ```HelloFilter```. If the function return value is ```Filter::STOP``` then stop Execute the remaining Filter and Action functions.
