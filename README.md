<div align="center">

![BunnyPHP](static/img/logo.png?raw=true)

BunnyPHP is a lightweight PHP MVC Framework.

[![Latest Stable Version](https://img.shields.io/packagist/v/ivanlulyf/bunnyphp.svg?color=orange&style=flat-square)](https://packagist.org/packages/ivanlulyf/bunnyphp)
[![Total Downloads](https://img.shields.io/packagist/dt/ivanlulyf/bunnyphp.svg?color=brightgreen&style=flat-square)](https://packagist.org/packages/ivanlulyf/bunnyphp)
![License](https://img.shields.io/packagist/l/ivanlulyf/bunnyphp.svg?color=blue&style=flat-square)

![Code Size](https://img.shields.io/github/languages/code-size/ivanlulyf/bunnyphp.svg?color=yellow&style=flat-square)
[![GitHub stars](https://img.shields.io/github/stars/ivanlulyf/bunnyphp.svg?style=social)](https://github.com/IvanLuLyf/BunnyPHP)

![PHP](https://img.shields.io/badge/PHP->%3D7.0.0-777bb3.svg?style=flat-square&logo=php)
[![Gitter](https://img.shields.io/gitter/room/ivanlulyf-bunnyphp/community.svg?style=flat-square&logo=gitter)](https://gitter.im/ivanlulyf-bunnyphp/community)

</div>

English | [中文](README_CN.md)

## Contents

- [Directory Structure](#directory-structure)
- [Installation](#installation)
  * [Using Composer](#using-composer)
  * [Using Clone](#using-clone)
- [Requirement](#requirement)
- [Server Setting](#server-setting)
- [Configure](#configure)
- [Framework Error Code](#framework-error-code)
- [Model](#model)
- [Controller and Router](#controller-and-router)
  * [Annotation](#annotation)

## Directory Structure
```
Project                 Root Dir
├─index.php             Entry File
├─api.php               Api Entry File
├─app                   Default Application Dir
│  ├─controller         Controller Dir
│  ├─model              Model Dir
│  ├─service            Service Dir
│  ├─filter             Filter Dir
├─BunnyPHP              Framework Dir
├─cache                 Default FileCache Dir
├─config                Default Configure Dir
│  ├─config.php         Default Configure File
├─static                Static Files Dir
├─template              Template Files Dir
├─upload                Default FileStorage Dir
```

## Installation
### Using Composer
```shell
composer create-project ivanlulyf/bunnyphp project --no-dev
```
### Using Clone
```shell
git clone https://github.com/IvanLuLyf/BunnyPHP.git
```

## Requirement

* PHP >= 7.0
* Database : MySQL SQLite or PostgreSQL

## Server Setting

> Apache

Add following content to ```.htacess``` file.

```apacheconfig
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . index.php
</IfModule>
```

> Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
```

## Configure

Sample

> PHP Config File
```php
<?php
return [
    "db"=> [
        "type"=>"sqlite",             // sqlite mysql pgsql
        "host"=>"",                   // database host
        "port"=>"",                   // database port
        "username"=>"",               // database username
        "password"=>"",               // database password
        "database"=>"sns.sqlite3",    // database name
        "prefix"=>"tp_",              // table prefix
    ],
    "storage" => [                    // storage config, optional
        "name" => "file",             // storage name,will load [name]Storage
    ],
    "cache" => [                      // cache config, optional
        "name" => "file",             // cache name,will load [name]Cache
    ],
    "logger" => [                     // logger config, optional
        "name" => "file",             // cache name,will load [name]Logger
        "filename" => "log.log",      // log file path,if using FileLogger
    ],
    "site_name"=>"Your Site Name",    // your site name
    "site_url"=>"YourDomain.com",     // your site domain
    "controller"=>"Index",            // default controller
];
```

> JSON Config File

to use this you should prevent other from getting this file.

```json
{
  "db":{
    "type":"sqlite",
    "host":"",
    "port":"",
    "username":"",
    "password":"",
    "database":"bunny.sqlite3",
    "prefix":"tp_"
  },
  "storage":{
    "name":"file"
  },
  "cache":{
    "name":"file"
  },
  "logger":{
    "name":"file",
    "filename":"log.log"
  },
  "site_name":"Your Site Name",
  "site_url":"YourDomain.com",
  "controller":"Index"
}
```

## Framework Error Code

|Code|Description|
|:---:|---|
|0|ok|
|-1|network error|
|-2|mod does not exist|
|-3|action does not exist|
|-4|template does not exist|
|-5|template rendering error|
|-6|database error|
|-7|parameter cannot be empty|
|-8|internal error|

## Model

Every Model **must extend** ```Model```

> Sample

```php
class MessageModel extends Model
{
    protected $_column = [
        'id' => ['integer', 'not null'],
        'message' => ['text', 'not null'],
        'from' => ['varchar(32)', 'not null']
    ];

    protected $_pk = ['id']; // Primary Key

    protected $_ai = 'id';   // Auto Increment
    
    protected $_uk = [['message','from']];  //Unique Key List
}
```

Use ```MessageModel::create()``` to generate a table

> Use chained calls to fetch data

```php
$messages = (new MessageModel())->where('from = :f',['f'=>$from])
    ->order('id desc')
    ->limit($size,$start)
    ->fetchAll(['message']);
```

> Add data

```php
$id = (new MessageModel())->add(['message'=>$message,'from'=>$from]);
```

> Update data

```php
$affect_rows = (new MessageModel())->where('from = :f',['f'=>$from])
    ->update(['message'=>'new message']);
```

> Delete data

```php
$affect_rows = (new MessageModel())->where('from = :f',['f'=>$from])->delete();
```

> Table Join

```php
join(ModelClass,[Join Condition(optianal)],[Table Field (optional)],[Join Method])
```

Join Condition Format

|Format Type|Sample|Description|
|----|----|----|
|String|```['id',]```|JoinedTable.id=CurrentTable.id|
|Array|```[['id','msg_id'],]```|JoinedTable.id=CurrentTable.msg_id|
|Key-value Pair|```['id'=>1]```|JoinedTable.id = 1|

Sample

```php
$hellos = $this->join(TestModel::class, [['id', 'msg_id']], ['message'])
    ->fetchAll(['content', 'id']);
```

Generated SQL(table prefix is tp_)

```sql
select tp_hello.content,tp_hello.id,tp_test.message from tp_hello left join tp_test on (tp_test.id=tp_hello.msg_id); 
```

## Controller and Router

Every Controller **must extend** ```Controller```

> Sample

```php
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
class TestController{
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

例如

```php
class TestController{
    /**
     * @filter test
     * @filter hello
     */
    public function ac_test(){
    
    }
}
```

It will call ```TestFilter```'s```doFilter``` function first.If the return value is ```Filter::NEXT``` then execute the next filter, in the example it is ```HelloFilter```. If the function return value is ```Filter::STOP``` then stop Execute the remaining Filter and Action functions.