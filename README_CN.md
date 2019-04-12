# BunnyPHP

BunnyPHP是一个轻量的PHP MVC框架.

[![Latest Stable Version](https://img.shields.io/packagist/v/ivanlulyf/bunnyphp.svg?color=orange)](https://packagist.org/packages/ivanlulyf/bunnyphp)
[![Total Downloads](https://img.shields.io/packagist/dt/ivanlulyf/bunnyphp.svg?color=brightgreen)](https://packagist.org/packages/ivanlulyf/bunnyphp)
![License](https://img.shields.io/packagist/l/ivanlulyf/bunnyphp.svg?color=blue)

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
├─BunnyPHP              框架目录
├─cache                 默认缓存目录
├─config                默认配置目录
│  ├─config.php         默认配置文件
├─static                静态资源目录
├─template              模板目录
├─upload                默认上传目录
```

## 安装
### 使用Composer安装
```shell
composer create-project ivanlulyf/bunnyphp project --no-dev
```
### 使用Git Clone安装
```shell
git clone https://github.com/IvanLuLyf/BunnyPHP.git
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
    "db"=> [
        "type"=>"sqlite",             // 可使用sqlite mysql pgsql
        "host"=>"",                   // 数据库服务器
        "port"=>"",                   // 数据库端口
        "username"=>"",               // 数据库用户名
        "password"=>"",               // 数据库密码
        "database"=>"sns.sqlite3",    // 数据库名
        "prefix"=>"tp_",              // 数据表前缀
    ],
    "site_name"=>"Your Site Name",    // 站点名称
    "site_url"=>"YourDomain.com",     // 站点域名
    "controller"=>"Index",            // 默认加载的控制器
];
```

> JSON配置文件

使用JSON文件时请保证配置文件不会被外部直接获取.

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

## 模型类

所有模型类 **必须extend** ```Model```

> 样例

```php
class MessageModel extends Model
{
    protected $_column = [
        'id' => ['integer', 'not null'],
        'message' => ['text', 'not null'],
        'from' => ['varchar(32)', 'not null']
    ];

    protected $_pk = ['id']; // 主键

    protected $_ai = 'id';   // 自增字段
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