# Quick start

## Requirement

* PHP >= 7.0
* Database : MySQL SQLite or PostgreSQL

## Using Composer

### Create a project by template

```bash
composer create-project ivanlulyf/bunnyphp-app PROJECT_NAME --no-dev
```

### Create a project by your own

> Run the command to get BunnyPHP

```bash
composer require ivanlulyf/bunnyphp
```

> Create file ```index.php``` with the  following content.

```php
<?php
define('APP_PATH', __DIR__ . '/');
define("IN_TWIMI_PHP", "True", TRUE);
require 'vendor/autoload.php';
(new BunnyPHP\BunnyPHP())->run();
``` 

## Using Clone

> Clone the repo to your project root

```bash
git clone https://github.com/IvanLuLyf/BunnyPHP.git
```

> Create file ```index.php``` with the  following content.

```php
<?php
define('APP_PATH', __DIR__ . '/');
define("IN_TWIMI_PHP", "True", TRUE);
require APP_PATH . 'BunnyPHP/BunnyPHP.php';
(new BunnyPHP\BunnyPHP())->run();
```