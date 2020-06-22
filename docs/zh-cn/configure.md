# 配置

样例

## PHP配置文件
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

## JSON配置文件

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
