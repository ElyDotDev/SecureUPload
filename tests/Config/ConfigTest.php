<?php

namespace Alirdn\SecureUPloadTest;

use Alirdn\SecureUPload\Config\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    public static $simple_config_object;
    public static $parse_config_object;

    public function setUp()
    {
        self::$simple_config_object = new Config();
        $simple_config_reflection   = new \ReflectionClass(self::$simple_config_object);
        $simple_config_property     = $simple_config_reflection->getProperty('config');
        $simple_config_property->setAccessible(true);
        $simple_config_property->setValue(self::$simple_config_object, array(
            'foo' => 'bar',
            'baz' => 'qux'
        ));


        self::$parse_config_object = new Config();
        $parse_config_reflection   = new \ReflectionClass(self::$parse_config_object);
        $default_config_property   = $parse_config_reflection->getProperty('default_config');
        $default_config_property->setAccessible(true);
        $default_config_property->setValue(self::$parse_config_object, array(
            'foo'   => 'bar',
            'baz'   => 123,
            'quux'  => array(),
            'xyzzy' => 'waldo',
            'corge' => 456
        ));

        $config_property = $parse_config_reflection->getProperty('config');
        $config_property->setAccessible(true);
        $config_property->setValue(self::$parse_config_object, array(
            'foo'   => '',
            'baz'   => 'non_numeric',
            'quux'  => 'not_array',
            'xyzzy' => '',
            'corge' => 'non_numeric'
        ));

        $validation_rules_property = $parse_config_reflection->getProperty('config_validation_rules');
        $validation_rules_property->setAccessible(true);
        $validation_rules_property->setValue(self::$parse_config_object, array(
            'foo'   => 'required',
            'baz'   => 'numeric',
            'quux'  => 'array',
            'xyzzy' => 'in_array:waldo,fred',
            'corge' => 'required|numeric'
        ));

        self::$parse_config_object->parse();
    }

    public function testGetExistConfigIndex()
    {
        $this->assertEquals('bar', self::$simple_config_object->get('foo'));
        $this->assertEquals('qux', self::$simple_config_object->get('baz'));
    }

    public function testGetNonExistConfigIndex()
    {
        $this->assertEquals('', self::$simple_config_object->get('quux'));
    }

    public function testSetMethod()
    {
        self::$simple_config_object->set('foo', 'xyzzy');
        $this->assertEquals('xyzzy', self::$simple_config_object->get('foo'));
        self::$simple_config_object->set('foo', 'bar');
    }

    public function testSetArrayMethod()
    {
        self::$simple_config_object->setArray(array(
            'foo'  => 'xyzzy',
            'baz'  => 'qux',
            'quux' => 'waldo'
        ));

        $this->assertEquals('xyzzy', self::$simple_config_object->get('foo'));
        $this->assertEquals('qux', self::$simple_config_object->get('baz'));
        $this->assertEquals('', self::$simple_config_object->get('quuz'));
    }

    public function testParsedConfigValues()
    {
        $this->assertEquals('bar', self::$parse_config_object->get('foo'));
        $this->assertEquals(123, self::$parse_config_object->get('baz'));
        $this->assertEquals(array(), self::$parse_config_object->get('quux'));
        $this->assertEquals('waldo', self::$parse_config_object->get('xyzzy'));
        $this->assertEquals(456, self::$parse_config_object->get('corge'));
    }
}