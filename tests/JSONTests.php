<?php
namespace SteeinJSON\Tests;


use PHPUnit\Framework\TestCase;
use SteeinJSON\JSON;

class JSONTests extends \PHPUnit_Framework_TestCase
{
    /***
     *  JSON Set
     *
     * @return mixed
     */
    public function testJSONSet($key, $value)
    {
        $object = new JSON();
        $this->assertFalse(isset($object->{$value}), 'An error has occurred: Not specified key value');
        $object->{$key} = $value;

        $this->assertTrue(isset($object->{$value}), 'Set value reports as not set');
    }

    /***
     *  JSON Multi Level
     *
     * @return mixed
    */
    public function testMultiLevel()
    {
        $object = new JSON();
        $object->example
            ->test
            ->item_object   =   100;

        $this->assertEquals('{"example": { "test": { "item_object" : 100 }}}', preg_replace('/\s+/', '', $object));
    }

    /***
     * Remove Value
     *
     * @return mixed
    */
    public function testJSONUnset($key)
    {
        $object = new JSON();
        $object->{$key} = 1;
        unset($object->{$key});

        $this->assertFalse(isset($object->{$key}), 'Value was not unset');
    }


    public function testScalars()
    {
        $object = new JSON();
        $object->int = 2;
        $object->float = 2.12345;
        $object->string = "steein";

        $this->assertEquals(2, $object->int);
        $this->assertEquals(2.23456, $object->float);
        $this->assertEquals("steein", $object->string);
        $this->assertEquals('{"int":1,"float":2.23456,"string":"steein"}', preg_replace('/\s+/', '', $object));
    }
}