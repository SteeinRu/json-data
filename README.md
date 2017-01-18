## SteeinJSON Data

This library is an object-oriented interface that works with data in JSON format

### Usage

#### Example 1


```php

use SteeinJSON\JSON;

$myJson = "
{
  "steein":
  {
    "item":"value"
  }
}
";
$object = new JSON($myJson);
```

#### Example 2
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
### Working with existing objects

If you would like to work with an existing object, rather than parsing a string, pass this object as the third constructor argument:

```php
use SteeinJSON\JSON;

$object = new JSON(null, null, $customJSON);



///Validating Against a Schema
$object->check($customSchema, [$extraSchemaURI => $extraSchemaData]);

```





#### Author: Shamsudin Serderov
