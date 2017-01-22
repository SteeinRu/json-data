<?php

include_once '../src/autoload.php';
include_once '../vendor/autoload.php';

use SteeinJSON\JSON;



$foo = new JSON;




echo '<pre>';
    print_r($foo->count());
echo '<pre>';