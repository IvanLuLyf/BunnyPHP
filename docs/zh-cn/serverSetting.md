# 服务器设置

## Apache

添加如下内容到 `.htaccess` 文件。

```apacheconfig
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . index.php
</IfModule>
```

### 可选：VirtualHost（推荐）

```apacheconfig
<VirtualHost *:80>
    ServerName example.local
    DocumentRoot "/path/to/project"
    <Directory "/path/to/project">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
```

### Nginx + PHP-FPM（常用）

```nginx
location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass 127.0.0.1:9000;
}
```

### 基础安全限制（常用）

```nginx
location ~ /\.(?!well-known) {
    deny all;
}

location ~* /(vendor|storage|runtime|cache|logs)/ {
    deny all;
}

location ~* \.(env|ini|log|sql|bak)$ {
    deny all;
}
```

## PHP 内置服务器（开发用）

```bash
php -S 127.0.0.1:8000 index.php
```

## Caddy（常用替代）

如果你希望比 Nginx/Apache 更简单的配置方式，Caddy 是一个很常见的选择。

`Caddyfile`（PHP-FPM）：

```caddyfile
:80
root * /path/to/project
php_fastcgi 127.0.0.1:9000
file_server
```

## OpenResty / Traefik（网关层）

- OpenResty 本质是带 Lua 扩展的 Nginx（仍属于 Nginx 生态）。
- Traefik 常用于 Docker/微服务场景的反向代理/入口网关。

### OpenResty（可直接用）

OpenResty 的配置语法与 Nginx 兼容。最小 `server` 示例：

```nginx
server {
    listen 80;
    server_name example.local;
    root /path/to/project;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass 127.0.0.1:9000;
    }
}
```

### Traefik + Docker Compose（可直接用）

最小示例：Traefik 将 `http://app.localhost` 路由到 `nginx` 容器，`nginx` 再把 PHP 请求转发给 `php-fpm`。

`docker-compose.yml`：

```yaml
services:
  traefik:
    image: traefik:v3.0
    command:
      - --api.insecure=true
      - --providers.docker=true
      - --entrypoints.web.address=:80
    ports:
      - "80:80"
      - "8080:8080"
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro

  nginx:
    image: nginx:alpine
    volumes:
      - ./:/var/www/html
      - ./nginx.conf:/etc/nginx/conf.d/default.conf:ro
    labels:
      - traefik.enable=true
      - traefik.http.routers.bunny.rule=Host(`app.localhost`)
      - traefik.http.routers.bunny.entrypoints=web
      - traefik.http.services.bunny.loadbalancer.server.port=80

  php:
    image: php:8.2-fpm-alpine
    volumes:
      - ./:/var/www/html
```

`nginx.conf`：

```nginx
server {
    listen 80;
    server_name _;
    root /var/www/html;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass php:9000;
    }
}
```
