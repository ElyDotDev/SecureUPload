<?php
namespace Alirdn\SecureUPloadTest;

use Alirdn\SecureUPload\Config\SecureUPloadConfig;
use Alirdn\SecureUPload\Config\UploadConfig;
use Alirdn\SecureUPload\Upload\Upload;
use Alirdn\SecureUPload\Validators\UploadValidator;
use org\bovigo\vfs\vfsStream;

include_once dirname(dirname(__FILE__)) . '/__custom_functions/uploadvalidator_class.php';

class UploadValidatorTest extends \PHPUnit_Framework_TestCase
{
    public static $fs;
    public static $upload_validator_obj;
    public static $upload_not_uploaded_by_http_post;
    public static $upload_forbidden_extension;
    public static $upload_logo_object;
    public static $upload_mime_type_object;

    public function setUp()
    {
        if ( ! self::$fs) {
            self::$fs = vfsStream::setup('temporary');
            vfsStream::copyFromFileSystem(dirname(dirname(__FILE__)) . '/__test_files');

            self::$upload_validator_obj = new UploadValidator;

            self::$upload_not_uploaded_by_http_post = new Upload(array(
                'name'     => 'foo.bar',
                'type'     => 'foo/bar',
                'tmp_name' => vfsStream::url('temporary/foo.bar'),
                'error'    => 0,
                'size'     => 0
            ));

            self::$upload_forbidden_extension = new Upload(array(
                'name'     => 'forbidden.php',
                'type'     => 'text/php',
                'tmp_name' => vfsStream::url('temporary/forbidden.php'),
                'error'    => 0,
                'size'     => 0
            ));

            self::$upload_logo_object = new Upload(array(
                'name'     => 'logo.png',
                'type'     => 'image/png',
                'tmp_name' => vfsStream::url('temporary/logo.png'),
                'error'    => 0,
                'size'     => 4535
            ));

            self::$upload_mime_type_object = new Upload(array(
                'name'     => 'mime-type.jpg',
                'type'     => 'image/jpeg',
                'tmp_name' => vfsStream::url('temporary/mime-type.jpg'),
                'error'    => 0,
                'size'     => 4
            ));


        }
    }

    public function testValidateMethodNotUploadedByHTTPPost()
    {
        $secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary') . '/'
            )
        );

        $this->assertEquals('11', self::$upload_validator_obj->validate(self::$upload_not_uploaded_by_http_post, $secureupload_config));
    }

    public function testValidateMethodMinimumFileSize()
    {
        $secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary') . '/',
                'min_filesize'  => 10576
            )
        );

        $this->assertEquals('12', self::$upload_validator_obj->validate(self::$upload_logo_object, $secureupload_config));
    }

    public function testValidateMethodMaximumFileSize()
    {
        $secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary') . '/',
                'max_filesize'  => 4000
            )
        );

        $this->assertEquals('13', self::$upload_validator_obj->validate(self::$upload_logo_object, $secureupload_config));
    }

    public function testValidateMethodForbiddenExtension()
    {
        $secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary') . '/'
            )
        );

        $this->assertEquals('14', self::$upload_validator_obj->validate(self::$upload_forbidden_extension, $secureupload_config));
    }

    public function testValidateMethodNotAcceptableExtension()
    {
        $secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary') . '/',
                'file_types'    => array('jpg' => 'image/jpeg')
            )
        );

        $this->assertEquals('15', self::$upload_validator_obj->validate(self::$upload_logo_object, $secureupload_config));
    }

    public function testValidateMethodNotAcceptableMimeType()
    {
        $secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary') . '/',
                'file_types'    => array('jpg' => 'image/jpeg')
            )
        );

        $this->assertEquals('16', self::$upload_validator_obj->validate(self::$upload_mime_type_object, $secureupload_config));
    }

    public function testValidateMethodSuccess()
    {
        $secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary') . '/',
                'file_types'    => array('png' => 'image/png'),
                'min_filesize'  => 4000,
                'max_filesize'  => 5000
            )
        );

        $this->assertEquals('10', self::$upload_validator_obj->validate(self::$upload_logo_object, $secureupload_config));
    }

    public function testValidateMethodSuccessWithUploadConfig()
    {
        $secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary') . '/'
            )
        );

        $upload_config = new UploadConfig(array(
            'file_types'   => array('png' => 'image/png'),
            'min_filesize' => 4000,
            'max_filesize' => 5000
        ));

        $this->assertEquals('10', self::$upload_validator_obj->validate(self::$upload_logo_object, $secureupload_config, $upload_config));
    }

    public function testValidateMethodMinimumFileSizeWithUploadConfig()
    {
        $secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary') . '/'
            )
        );

        $upload_config = new UploadConfig(array(
            'min_filesize' => 10576
        ));

        $this->assertEquals('12', self::$upload_validator_obj->validate(self::$upload_logo_object, $secureupload_config, $upload_config));
    }

    public function testValidateMethodMaximumFileSizeWithUploadConfig()
    {
        $secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary') . '/'
            )
        );

        $upload_config = new UploadConfig(array(
            'max_filesize' => 4000
        ));

        $this->assertEquals('13', self::$upload_validator_obj->validate(self::$upload_logo_object, $secureupload_config, $upload_config));
    }

    public function testValidateMethodNotAcceptableExtensionWithUploadConfig()
    {
        $secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary') . '/'
            )
        );

        $upload_config = new UploadConfig(array(
            'file_types' => array('jpg' => 'image/jpeg')
        ));

        $this->assertEquals('15', self::$upload_validator_obj->validate(self::$upload_logo_object, $secureupload_config, $upload_config));
    }

    public function testValidateMethodNotAcceptableMimeTypeWithUploadConfig()
    {
        $secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary') . '/'
            )
        );

        $upload_config = new UploadConfig(array(
            'file_types' => array('jpg' => 'image/jpeg')
        ));

        $this->assertEquals('16', self::$upload_validator_obj->validate(self::$upload_mime_type_object, $secureupload_config, $upload_config));
    }

}