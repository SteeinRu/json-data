<?php
namespace SteeinJSON;

use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;
use SteeinJSON\Exceptions\InvalidDataException;
use SteeinJSON\Exceptions\NotFoundException;
use SteeinJSON\Exceptions\ParseException;
use SteeinJSON\Exceptions\SchemaException;
use SteeinJSON\Exceptions\TypeException;
use SteeinJSON\Exceptions\ValidationException;
use SteeinJSON\Stub\DummyClass;

/****
 * This library is an object-oriented interface that works with data in JSON format Edit
 *
 * @package SteeinJSON
 *
 * @author      Shamsudin Serderov
 * @url         https://www.steein.ru
 *
 * @copyright   2017 - Steein Inc
 * @version     PHP 7 >=
 */
class JSON extends JSONConfig
{
    /***
     * The array to store all the data
     *
     * @var array
    */
    protected $storage;

    /***
     * Error checking
     *
     * @var array
    */
    protected $validationErrors = [];

    /**
     * Create a new object for manipulation
     *
     * @param null $json
     * @param null $config
     * @param null $useObject
     * @throws ParseException
     * @throws TypeException
     */
    public function __construct($json = null, $config = null, &$useObject = null)
    {
        //If the variable is equal to the "true"
        // generally produces data fusion
        if ($config !== null)
        {
            if (!is_array($config))
                throw new TypeException("Config must be an array");
            $this->config = array_merge($this->config, $config);
        }

        // We will use the facilities provided
        if ($useObject !== null) {
            if (!is_object($useObject)) {
                throw new TypeException("You must provide an object");
            }
            $this->storage =& $useObject;
        }
        elseif (isset($json))
        {
            if (!is_string($json)) {
                throw new TypeException("JSON input must be provided as a string");
            }
            $this->storage = $this->decode($json);
            if (json_last_error() !== \JSON_ERROR_NONE) {
                throw new ParseException(json_last_error_msg(), json_last_error());
            }
        }
        //If nothing has been provided that create a new object
        else
            $this->storage = new DummyClass();
    }

    /**
     * Get a value for the given key
     *
     * @param $key  = specify the name of the key
     * @return JSON
     * @throws NotFoundException
     */
    public function __get($key)
    {
        if (!isset($this->storage->{$key}))
        {
            if (!$this->config['create-missing'])
                throw new NotFoundException("Key does not exist");

            $this->storage->{$key} = new DummyClass();
            return new self(null, $this->config, $this->storage->{$key});
        }

        if (is_object($this->storage->{$key}))
            return new self(null, $this->config, $this->storage->{$key});
        else
            return $this->storage->{$key};
    }

    /**
     * Set the new value for the key
     *
     * @param $key    =   new key
     * @param $value  =   new value
     * @throws InvalidDataException
     */
    public function __set($key, $value)
    {
        if (!is_scalar($value) && !is_array($value) && !is_object($value)) {
            throw new InvalidDataException("Value must be a scalar, an array, or an object");
        }
        $this->storage->{$key} = $value;
    }

    /**
     * Check method, the key is installed or not
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key) : bool
    {
        return property_exists($this->storage, $key);
    }

    /**
     * To delete a specific value on a key
     *
     * @param $key
     * @return mixed
     * @throws NotFoundException
     */
    public function __unset($key)
    {
        if(empty($key))
            throw new NotFoundException('Undefined key');
        elseif(isset($this->storage->{$key}))
            unset($this->storage->{$key});
    }

    /**
     * Convert JSON to String
     *
     * @see toString
     */
    public function __toString()
    {
        return $this->encode($this->storage, $this->config['encode']) ?: "";
    }

    /***
     * Save the object to a file
     *
     * @param null $path
     * @return string
     */
    public function save($path = null)
    {
        if(\file_exists($path) == false)
        {
            try
            {
                \file_put_contents($path, $this->formattedJson($this->encode($this->storage)));
            }catch (\ErrorException $error) {
                return $error->getMessage();
            }
        }
    }

    /**
     * Check the current document with the scheme
     *
     * @param string $schema Schema to validate against
     * @param array $schemaData Additional schema resources
     *
     * @throws SchemaException
     * @throws TypeException
     * @throws ValidationException
     *
     * @return bool
     */
    public function check($schema, $schemaData = null)
    {
        $schemaStorage = new SchemaStorage();
        if ($schemaData != null)
        {
            if (!is_array($schemaData)) {
                throw new TypeException("Schema data must be provided as an array");
            }

            foreach ($schemaData as $ref => $data)
            {
                if (is_string($data) || $schema instanceof DummyClass)
                    $data = $this->decode($data);
                else
                    throw new TypeException('Schema must be a string or DummyClass object');

                if (json_last_error() !== \JSON_ERROR_NONE) {
                    throw new SchemaException("Unable to parse schema $ref: " . json_last_error_msg());
                }

                $schemaStorage->addSchema($ref, $data);
            }
        }

        if (is_string($schema) || $schema instanceof DummyClass)
        {
            $schema = $this->decode($schema);

            if (json_last_error() !== \JSON_ERROR_NONE)
                throw new SchemaException('Unable to parse root schema');

        } else {
            throw new TypeException('Schema must be a string or DummyClass object');
        }

        // check a document
        try
        {
            $v = new Validator(new Factory($schemaStorage));
            $v->check($this->storage, $schema);
        } catch (\Exception $e) {
            throw new ValidationException("Internal validation failure", 0, $e);
        }

        // Save the error and returns the result
        $this->validationErrors = $v->getErrors();
        if ($this->config['validation-exceptions'])
        {
            $err = $this->validationErrors[0];
            $msg = sprintf("%s: %s", $err['pointer'], $err['message']);
            throw new ValidationException($msg);
        }

        return $v->isValid();
    }

    /**
     * Get a list of errors from the most recent validation attempt
     *
     * @return array
     */
    public function errors()
    {
        return $this->validationErrors;
    }

    /***
     *  Decodes a JSON string
     *
     * @param $value
     * @param int $options
     * @param int $depth
     *
     * @return mixed
     */
    public function encode($value, $options = 0, $depth = 512)
    {
        return json_encode($value, $options = 0, $depth = 512);
    }

    /***
     *  Encodes a JSON string
     *
     * @param $json
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     *
     * @return mixed
     */
    public function decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        return json_decode($json, $assoc, $depth, $options);
    }

    /***
     * Indents a flat JSON string to make it more human-readable.
     *
     * @param bool $json
     * @return string
     */
    protected function formattedJson($json = false)
    {
        $result      = '';
        $pos         = 0;
        $strLen      = strlen($json);
        $indentStr   = '  ';
        $newLine     = "\n";
        $prevChar    = '';
        $outOfQuotes = true;

        for ($i=0; $i<=$strLen; $i++) {

            // Grab the next character in the string.
            $char = substr($json, $i, 1);

            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;

                // If this character is the end of an element,
                // output a new line and indent the next line.
            } else if(($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos --;
                for ($j=0; $j<$pos; $j++) {
                    $result .= $indentStr;
                }
            }

            // Add the character to the result string.
            $result .= $char;

            // If the last character was the beginning of an element,
            // output a new line and indent the next line.
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos ++;
                }

                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }

            $prevChar = $char;
        }

        return $result;
    }
}