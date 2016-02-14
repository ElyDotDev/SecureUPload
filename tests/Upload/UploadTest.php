<?php

namespace Alirdn\SecureUPloadTest;

use Alirdn\SecureUPload\Upload\Upload;

class UploadTest extends \PHPUnit_Framework_TestCase
{
    public static $raw_object;
    public static $tmp_info_object;
    public static $test_object;

    public function setUp()
    {
        self::$raw_object      = new Upload();
        self::$tmp_info_object = new Upload(array(
            'name'     => 'foo_bar.baz',
            'tmp_name' => 'tmp/foobar.baz',
            'type'     => 'foo/bar',
            'error'    => 0,
            'size'     => 1024,
        ));
        self::$test_object     = new Upload();
    }

    public function testRawObjectTmpInfoName()
    {
        $this->assertEquals('', self::$raw_object->getTmpInfo('name'));
    }

    public function testRawObjectTmpInfoTmpName()
    {
        $this->assertEquals('', self::$raw_object->getTmpInfo('tmp_name'));
    }

    public function testRawObjectTmpInfoType()
    {
        $this->assertEquals('', self::$raw_object->getTmpInfo('type'));
    }

    public function testRawObjectTmpInfoError()
    {
        $this->assertEquals(4, self::$raw_object->getTmpInfo('error'));
    }

    public function testRawObjectTmpInfoSize()
    {
        $this->assertEquals(0, self::$raw_object->getTmpInfo('size'));
    }

    public function testRawObjectPropertyStatus()
    {
        $this->assertEquals(0, self::$raw_object->status);
    }

    public function testRawObjectPropertyError()
    {
        $this->assertEquals(0, self::$raw_object->error);
    }

    public function testRawObjectPropertySize()
    {
        $this->assertEquals(0, self::$raw_object->size);
    }

    public function testRawObjectPropertyName()
    {
        $this->assertEquals('', self::$raw_object->name);
    }

    public function testRawObjectPropertyExt()
    {
        $this->assertEquals('', self::$raw_object->ext);
    }

    public function testRawObjectPropertyRelativePath()
    {
        $this->assertEquals('', self::$raw_object->relative_path);
    }

    public function testRawObjectPropertyRelativeUrl()
    {
        $this->assertEquals('', self::$raw_object->relative_url);
    }

    public function testRawObjectPropertyPath()
    {
        $this->assertEquals('', self::$raw_object->path);
    }

    public function testRawObjectPropertyType()
    {
        $this->assertEquals('', self::$raw_object->type);
    }

    public function testTmpInfoObjectTmpInfoName()
    {
        $this->assertEquals('foo_bar.baz', self::$tmp_info_object->getTmpInfo('name'));
    }

    public function testTmpInfoObjectTmpInfoTmpName()
    {
        $this->assertEquals('tmp/foobar.baz', self::$tmp_info_object->getTmpInfo('tmp_name'));
    }

    public function testTmpInfoObjectTmpInfoType()
    {
        $this->assertEquals('foo/bar', self::$tmp_info_object->getTmpInfo('type'));
    }

    public function testTmpInfoObjectTmpInfoError()
    {
        $this->assertEquals(0, self::$tmp_info_object->getTmpInfo('error'));
    }

    public function testTmpInfoObjectTmpInfoSize()
    {
        $this->assertEquals(1024, self::$tmp_info_object->getTmpInfo('size'));
    }

    public function testSetTmpInfoName()
    {
        self::$test_object->setTmpInfo('name', 'foo_bar.baz');
        $this->assertEquals('foo_bar.baz', self::$test_object->getTmpInfo('name'));
    }

    public function testSetTmpInfoTmpName()
    {
        self::$test_object->setTmpInfo('tmp_name', 'tmp/foo.baz');
        $this->assertEquals('tmp/foo.baz', self::$test_object->getTmpInfo('tmp_name'));
    }

    public function testSetTmpInfoTmpType()
    {
        self::$test_object->setTmpInfo('type', 'foo/bar');
        $this->assertEquals('foo/bar', self::$test_object->getTmpInfo('type'));
    }

    public function testSetTmpInfoError()
    {
        self::$test_object->setTmpInfo('error', 0);
        $this->assertEquals(0, self::$test_object->getTmpInfo('error'));
    }

    public function testSetTmpInfoSize()
    {
        self::$test_object->setTmpInfo('size', 1024);
        $this->assertEquals(1024, self::$test_object->getTmpInfo('size'));
    }

    public function testGetTmpInfoNonSetIndex()
    {
        $this->assertEquals('', self::$test_object->getTmpInfo('non_set_index'));
    }

}