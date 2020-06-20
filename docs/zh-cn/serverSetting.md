# 服务器设置

## Apache

添加如下内容到```.htacess```文件.

```apacheconfig
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . index.php
</IfModule>
```

## Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
```
