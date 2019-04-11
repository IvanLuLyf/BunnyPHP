# BunnyPHP

BunnyPHP is a lightweight PHP MVC Framework.

[![Latest Stable Version](https://img.shields.io/packagist/v/ivanlulyf/bunnyphp.svg?color=orange)](https://packagist.org/packages/ivanlulyf/bunnyphp)
[![Total Downloads](https://img.shields.io/packagist/dt/ivanlulyf/bunnyphp.svg?color=brightgreen)](https://packagist.org/packages/ivanlulyf/bunnyphp)
![License](https://img.shields.io/packagist/l/ivanlulyf/bunnyphp.svg?color=blue)

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

## Controller and Router

Every Controller **must extend** ```Controller```

> Sample

```php
class MessageController extends Controller
{
    public function ac_init_cli()
    {
        MessageModel::create();    //Create Table 'prefix_message'
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

In the terminal, enter ```php cli message init``` and the request will be responded by ```MessageController``` if it exists.

If there is a function ```ac_init_cli``` in ```MessageController```, the request will be responded by the function.
 
If it does not exist, it will be responded by ```ac_init```.

If they do not exist, an error will be reported.

> Web

In Browser, the request ```/message/list``` will be responded by ```MessageController::ac_list``` if it exists.

What's more. If the function of a particular request method exists,such as ```ac_message_get```, ```ac_message_post```, or ```ac_message_put```, it will be called first.

If it does not exist, it will be responded by ```ac_message```.

If they do not exist, an error will be reported.

> API

The API Request start with ```/api/```,such as ```/api/message/list```.

It will be displayed in JSON format.

> AJAX

The API Request start with ```/ajax/```,such as ```/ajax/message/list```.

It will be displayed in JSON format.

