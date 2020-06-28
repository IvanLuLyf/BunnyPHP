# 模型类

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
