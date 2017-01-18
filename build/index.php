<?php

include_once '../src/autoload.php';
include_once '../vendor/autoload.php';

use SteeinJSON\JSON;

$jsons = "{
  \"example\":{
    \"item\":\"value\"
  }
}";

$json = new JSON($jsons);

echo $json;