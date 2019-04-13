# BunnyPHP

![BunnyPHP](static/img/logo.png?raw=true)

BunnyPHP is a lightweight PHP MVC Framework.

[![Latest Stable Version](https://img.shields.io/packagist/v/ivanlulyf/bunnyphp.svg?color=orange)](https://packagist.org/packages/ivanlulyf/bunnyphp)
[![Total Downloads](https://img.shields.io/packagist/dt/ivanlulyf/bunnyphp.svg?color=brightgreen)](https://packagist.org/packages/ivanlulyf/bunnyphp)
![License](https://img.shields.io/packagist/l/ivanlulyf/bunnyphp.svg?color=blue)

[中文](README_CN.md)

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
  "site_name":"Your Site Name",
  "site_url":"YourDomain.com",
  "controller":"Index"
}
```

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

