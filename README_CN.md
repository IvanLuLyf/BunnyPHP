<div align="center">

![BunnyPHP](https://github.com/bunniescc/media/blob/master/php.png?raw=true)

BunnyPHP是一个轻量的PHP MVC框架.

[![Latest Stable Version](https://img.shields.io/packagist/v/ivanlulyf/bunnyphp.svg?color=orange&style=flat-square)](https://packagist.org/packages/ivanlulyf/bunnyphp)
[![Total Downloads](https://img.shields.io/packagist/dt/ivanlulyf/bunnyphp.svg?color=brightgreen&style=flat-square)](https://packagist.org/packages/ivanlulyf/bunnyphp)
![License](https://img.shields.io/packagist/l/ivanlulyf/bunnyphp.svg?color=blue&style=flat-square)

![Code Size](https://img.shields.io/github/languages/code-size/ivanlulyf/bunnyphp.svg?color=yellow&style=flat-square)
[![GitHub stars](https://img.shields.io/github/stars/ivanlulyf/bunnyphp.svg?style=social)](https://github.com/IvanLuLyf/BunnyPHP)

![PHP](https://img.shields.io/badge/PHP->%3D7.0.0-777bb3.svg?style=flat-square&logo=php)
[![Gitter](https://img.shields.io/gitter/room/ivanlulyf-bunnyphp/community.svg?style=flat-square&logo=gitter)](https://gitter.im/ivanlulyf-bunnyphp/community)

</div>

[English](README.md) | 中文

[![Run on Repl.it](https://img.shields.io/badge/-run%20on%20repl.it-%235C6970?logo=repl.it&style=flat-square&logoColor=white)](https://repl.it/github/ivanlulyf/bunnyphp-app)
[![Open in Gitpod](https://img.shields.io/badge/-open%20in%20Gitpod-%231966D2?logo=gitpod&style=flat-square&logoColor=white)](https://gitpod.io/#https://github.com/IvanLuLyf/BunnyPHP-App)
[![Deploy to Heroku](https://img.shields.io/badge/-Deploy%20to%20Heroku-%237056BF?logo=heroku&style=flat-square&labelColor=%237056BF&logoColor=white)](https://heroku.com/deploy?template=https://github.com/IvanLuLyf/BunnyPHP-App)

## 目录

- [目录结构](#目录结构)
- [安装](#安装)
  * [使用Composer安装](#使用Composer安装)
  * [使用Git Clone安装](#使用Git-Clone安装)
- [环境要求](#环境要求)
- [服务器设置](#服务器设置)
- [配置](#配置)
- [框架错误码](#框架错误码)
- [模型类](#模型类)
- [控制器和路由](#控制器和路由)
  * [注解](#注解)

## 目录结构
```
Project                 根目录
├─index.php             入口文件
├─api.php               Api入口文件
├─app                   默认应用目录
│  ├─controller         控制器目录
│  ├─model              模型目录
│  ├─service            服务目录
│  ├─filter             过滤器目录
├─cache                 默认缓存目录
├─config                配置文件目录
│  ├─config.php         默认配置文件
├─lang                  语言包目录
├─static                静态资源目录
├─template              模板目录
├─upload                默认上传目录
```

## 安装
### 使用Composer安装

#### 用模板创建项目

```shell
composer create-project ivanlulyf/bunnyphp-app PROJECT_NAME --no-dev
```

#### 手动创建项目

> 运行命令获取BunnyPHP

```shell
composer require ivanlulyf/bunnyphp
```

> 创建文件```index.php```并写入如下代码

```php
<?php
define('APP_PATH', __DIR__ . '/');
define("IN_TWIMI_PHP", "True", TRUE);
require 'vendor/autoload.php';
(new BunnyPHP\BunnyPHP())->run();
``` 

### 使用Git Clone安装

> 将项目Clone到项目根目录

```shell
git clone https://github.com/IvanLuLyf/BunnyPHP.git
```

> 创建文件```index.php```并写入如下代码

```php
<?php
define('APP_PATH', __DIR__ . '/');
define("IN_TWIMI_PHP", "True", TRUE);
require APP_PATH . 'BunnyPHP/BunnyPHP.php';
(new BunnyPHP\BunnyPHP())->run();
``` 

## 环境要求

* PHP版本 >= 7.0
* 数据库: MySQL SQLite PostgreSQL

## 服务器设置

> Apache

添加如下内容到```.htacess```文件.

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

## 配置

样例

> PHP配置文件
```php
<?php
return [
    "namespace" => "\\App",           // 项目命名空间(可选,默认为空)
    "apps" => [                       // 子应用配置
        "[url path]" => [             // [url path] 子应用的访问路径
            "path" => "admin",        // 子应用的程序路径(可选,如果使用composer且子应用的命名空间不为空)
            "namespace" => "\\App"    // 子应用的命名空间
        ],
    ],
    "db"=> [
        "type"=>"sqlite",             // 可使用sqlite mysql pgsql
        "host"=>"",                   // 数据库服务器
        "port"=>"",                   // 数据库端口
        "username"=>"",               // 数据库用户名
        "password"=>"",               // 数据库密码
        "database"=>"bunny.sqlite3",  // 数据库名
        "prefix"=>"tp_",              // 数据表前缀
    ],
    "storage" => [                    // 存储配置, 可选
        "name" => "file",             // 存储类名字,会加载[名字]Storage
    ],
    "cache" => [                      // 缓存配置, 可选
        "name" => "file",             // 缓存类名字,会加载[名字]Cache
    ],
    "logger" => [                     // 日志配置, 可选
        "name" => "file",             // 日志类名字,会加载[名字]Logger
        "filename" => "log.log",      // 日志文件路径,如果使用FileLogger
    ],
    "site_name"=>"Your Site Name",    // 站点名称
    "site_url"=>"YourDomain.com",     // 站点域名
    "controller"=>"Index",            // 默认加载的控制器
];
```

> JSON配置文件

使用JSON文件时请**保证**配置文件不会被外部直接获取.

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

## 框架错误码

|Code|Description|
|:---:|---|
|0|ok|
|-1|网络错误|
|-2|Mod不存在|
|-3|Action不存在|
|-4|模板不存在|
|-5|模板渲染错误|
|-6|数据库错误|
|-7|参数不可为空|
|-8|内部错误|

## 模型类

所有模型类 **必须extend** ```Model```

> 样例

```php
<?php

use BunnyPHP\Model;

class MessageModel extends Model
{
    protected $_column = [
        'id' => ['integer', 'not null'],
        'message' => ['text', 'not null'],
        'from' => ['varchar(32)', 'not null']
    ];

    protected $_pk = ['id']; // 主键

    protected $_ai = 'id';   // 自增字段
    
    protected $_uk = [['message','from']];  //唯一键列表
}
```

使用```MessageModel::create()```来创建一个数据表

> 使用链式调用来获取数据

```php
$messages = (new MessageModel())->where('from = :f',['f'=>$from])
    ->order('id desc')
    ->limit($size,$start)
    ->fetchAll(['message']);
```

> 添加数据

```php
$id = (new MessageModel())->add(['message'=>$message,'from'=>$from]);
```

> 修改数据

```php
$affect_rows = (new MessageModel())->where('from = :f',['f'=>$from])
    ->update(['message'=>'new message']);
```

> 删除数据

```php
$affect_rows = (new MessageModel())->where('from = :f',['f'=>$from])->delete();
```

> 表连接

```php
join(模型类,[连接条件(可选)],[需要的表字段(可选)],[连接方式])
```

连接条件格式

|格式类型|样例|描述|
|----|----|----|
|字符串|```['id',]```|被连接表.id=当前表.id|
|数组|```[['id','msg_id'],]```|被连接表.id=当前表.msg_id|
|键值对|```['id'=>1]```|被连接表.id = 1|

样例

```php
$hellos = $this->join(TestModel::class, [['id', 'msg_id']], ['message'])
    ->fetchAll(['content', 'id']);
```

生成的SQL(数据表前缀为tp_)

```sql
select tp_hello.content,tp_hello.id,tp_test.message from tp_hello left join tp_test on (tp_test.id=tp_hello.msg_id); 
```

## 控制器和路由

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
