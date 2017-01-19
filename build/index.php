<?php

include_once '../src/autoload.php';
include_once '../vendor/autoload.php';

use SteeinJSON\JSON;



$json = new JSON();

$json->test->one->two = 's';

echo $json->getObject('test')->getObject('one')->getObject('two');