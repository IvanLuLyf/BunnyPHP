# Server Setting

## Apache

Add the following to `.htaccess`.

```apacheconfig
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . index.php
</IfModule>
```

### Optional: VirtualHost (recommended)

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

### Nginx + PHP-FPM (common)

```nginx
location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass 127.0.0.1:9000;
}
```

### Basic hardening (common)

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

## Built-in server (for development)

```bash
php -S 127.0.0.1:8000 index.php
```

## Caddy (common alternative)

If you prefer a simpler web server than Nginx/Apache, Caddy is a popular choice.

`Caddyfile` (PHP-FPM):

```caddyfile
:80
root * /path/to/project
php_fastcgi 127.0.0.1:9000
file_server
```

## OpenResty / Traefik (gateway layer)

- OpenResty is Nginx with Lua extensions (still Nginx ecosystem).
- Traefik is commonly used as a reverse-proxy / ingress in Docker or microservices.

### OpenResty (practical)

OpenResty config is compatible with Nginx syntax. A minimal `server` example:

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

### Traefik + Docker Compose (practical)

Minimal example: Traefik routes `http://app.localhost` to an `nginx` container, and `nginx` forwards PHP to `php-fpm`.

`docker-compose.yml`:

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

`nginx.conf`:

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
