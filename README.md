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
├─BunnyPHP              Framework Dir
├─cache                 Default FileCache Dir
├─config                Configure Dir
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

## Configure

Sample

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
    "action"=>"index",                // default action
];
```

## Model

Sample

```php
class UserModel extends Model
{
    protected $_column = [
        'uid' => ['integer', 'not null'],
        'username' => ['varchar(16)', 'not null'],
        'password' => ['varchar(32)', 'not null']
    ];

    protected $_pk = ['uid']; // Primary Key

    protected $_ai = 'uid';   // Auto Increment
}
```

Use ```UserModel::create()``` to generate a table

## Controller

Request```/ctrl/act``` will be handle by ```/app/CtrlController.php```.It will call ```CtrlController::ac_act()``` function by defalut.

If there has ```ac_act_get```,```ac_act_post```, Request```POST /ctrl/act/```will handle by ```ac_act_post```.

