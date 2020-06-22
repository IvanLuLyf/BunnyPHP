# Configure

Sample

## PHP Config File
```php
<?php
return [
    "namespace" => "\\App",           // project namespace(optional, empty by default)
    "apps" => [                       // configure for sub-app  
        "[url path]" => [             // [url path] sub-app's access path
            "path" => "admin",        // path to sub-app's program(optional, if using composer and sub-app's namespace is not empty)
            "namespace" => "\\App"    // sub-app's namespace
        ],
    ],
    "db"=> [
        "type"=>"sqlite",             // sqlite mysql pgsql
        "host"=>"",                   // database host
        "port"=>"",                   // database port
        "username"=>"",               // database username
        "password"=>"",               // database password
        "database"=>"bunny.sqlite3",  // database name
        "prefix"=>"tp_",              // table prefix
    ],
    "storage" => [                    // storage config, optional
        "name" => "file",             // storage name,will load [name]Storage
    ],
    "cache" => [                      // cache config, optional
        "name" => "file",             // cache name,will load [name]Cache
    ],
    "logger" => [                     // logger config, optional
        "name" => "file",             // cache name,will load [name]Logger
        "filename" => "log.log",      // log file path,if using FileLogger
    ],
    "site_name"=>"Your Site Name",    // your site name
    "site_url"=>"YourDomain.com",     // your site domain
    "controller"=>"Index",            // default controller
];
```

## JSON Config File

to use this you **should** prevent other from getting this file.

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

