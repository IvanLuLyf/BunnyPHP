# Server Setting

## Apache

Add following content to ```.htacess``` file.

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
