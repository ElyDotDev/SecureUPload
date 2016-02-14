<?php

namespace Alirdn\SecureUPloadTest;

use Alirdn\SecureUPload\Config\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public static $parser_object;
    public static $parsed_config;

    public function setUp()
    {
        self::$parser_object = new Parser(
            array(
                'foo'    => '',
                'baz'    => 'non_numeric',
                'quux'   => 'not_array',
                'xyzzy'  => '',
                'corge'  => 'non_numeric',
                'grault' => '4',
                'plugh'  => 'no_validation_rule'
            ),
            array(
                'foo'    => 'required',
                'baz'    => 'numeric',
                'quux'   => 'array',
                'xyzzy'  => 'in_array:waldo,fred',
                'corge'  => 'required|numeric',
                'grault' => 'required|in_array:1,2,3'
            ),
            array(
                'foo'    => 'bar',
                'baz'    => 123,
                'quux'   => array(),
                'xyzzy'  => 'waldo',
                'corge'  => 456,
                'grault' => '1'
            )
        );

        self::$parsed_config = self::$parser_object->parse();
    }

    public function testRequiredValidationRule()
    {
        $this->assertEquals('bar', self::$parsed_config['foo']);
    }

    public function testNumericValidationRule()
    {
        $this->assertEquals(123, self::$parsed_config['baz']);
    }

    public function testArrayValidationRule()
    {
        $this->assertEquals(array(), self::$parsed_config['quux']);
    }

    public function testInArrayValidationRule()
    {
        $this->assertEquals('waldo', self::$parsed_config['xyzzy']);
    }

    public function testMultipleValidationRulesSimple()
    {
        $this->assertEquals(456, self::$parsed_config['corge']);
    }

    public function testMultipleValidationRulesComplex()
    {
        $this->assertEquals('1', self::$parsed_config['grault']);
    }
}