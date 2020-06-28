# Model

Every Model **must extend** ```Model```

> Sample

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

    protected $_pk = ['id']; // Primary Key

    protected $_ai = 'id';   // Auto Increment
    
    protected $_uk = [['message','from']];  //Unique Key List
}
```

Use ```MessageModel::create()``` to generate a table

> Use chained calls to fetch data

```php
$messages = (new MessageModel())->where('from = :f',['f'=>$from])
    ->order('id desc')
    ->limit($size,$start)
    ->fetchAll(['message']);
```

> Add data

```php
$id = (new MessageModel())->add(['message'=>$message,'from'=>$from]);
```

> Update data

```php
$affect_rows = (new MessageModel())->where('from = :f',['f'=>$from])
    ->update(['message'=>'new message']);
```

> Delete data

```php
$affect_rows = (new MessageModel())->where('from = :f',['f'=>$from])->delete();
```

> Table Join

```php
join(ModelClass,[Join Condition(optianal)],[Table Field (optional)],[Join Method])
```

Join Condition Format

|Format Type|Sample|Description|
|----|----|----|
|String|```['id',]```|JoinedTable.id=CurrentTable.id|
|Array|```[['id','msg_id'],]```|JoinedTable.id=CurrentTable.msg_id|
|Key-value Pair|```['id'=>1]```|JoinedTable.id = 1|

Sample

```php
$hellos = $this->join(TestModel::class, [['id', 'msg_id']], ['message'])
    ->fetchAll(['content', 'id']);
```

Generated SQL(table prefix is tp_)

```sql
select tp_hello.content,tp_hello.id,tp_test.message from tp_hello left join tp_test on (tp_test.id=tp_hello.msg_id); 
```
