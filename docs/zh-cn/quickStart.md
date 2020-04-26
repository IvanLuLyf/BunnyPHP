# 快速开始

## 环境要求

* PHP版本 >= 7.0
* 数据库: MySQL SQLite PostgreSQL

## 使用Composer安装

### 用模板创建项目

```bash
composer create-project ivanlulyf/bunnyphp-app PROJECT_NAME --no-dev
```

### 手动创建项目

> 运行命令获取BunnyPHP

```bash
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

## 使用Git Clone安装

> 将项目Clone到项目根目录

```bash
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