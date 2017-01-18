## SteeinJSON Data

This library is an object-oriented interface that works with data in JSON format

### Usage

```php

use SteeinJSON\JSON;

$object = new JSON();
$object->text->item = 'value';
$object->text->item->one = 'value2';

//By default, simply output through "echo"
echo $object;

//At the request is allowed to maintain in a JSON file
$file = __DIR__.'/json/item.json';
$object->save($file);

```


#### Author: Shamsudin Serderov
