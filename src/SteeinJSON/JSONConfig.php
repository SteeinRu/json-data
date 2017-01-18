<?php
namespace SteeinJSON;


class JSONConfig
{
    /****
     * Default configuration
     *
     * @var array | \ArrayAccess
     */
    protected $config = [
        'encode' =>     JSON_NUMERIC_CHECK      |
            JSON_PRETTY_PRINT       |
            JSON_UNESCAPED_SLASHES  |
            JSON_UNESCAPED_UNICODE  |
            JSON_PARTIAL_OUTPUT_ON_ERROR,

        'create-missing' => true,

        'validation-exceptions' => false
    ];
}