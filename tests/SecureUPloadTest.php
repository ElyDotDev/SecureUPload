<?php

namespace Alirdn\SecureUPloadTest;

use Alirdn\SecureUPload\Config\SecureUPloadConfig;
use Alirdn\SecureUPload\Exceptions\UploadFolderException;
use Alirdn\SecureUPload\headersClass;
use Alirdn\SecureUPload\SecureUPload;
use org\bovigo\vfs\vfsStream;

include_once dirname( __FILE__ ) . '/__custom_functions/uploadmanager_class.php';
include_once dirname( __FILE__ ) . '/__custom_functions/fileupload_class.php';
include_once dirname( __FILE__ ) . '/__custom_functions/uploadvalidator_class.php';
include_once dirname( __FILE__ ) . '/__custom_functions/secureupload_class.php';

class SecureUPloadTest extends \PHPUnit_Framework_TestCase {
	public static $fs;
	public static $secureupload_obj;

	public function setUp() {
		if ( ! self::$secureupload_obj ) {
			self::$fs = vfsStream::setup( 'temporary', null, array(
				'uploads' => array()
			) );
			vfsStream::copyFromFileSystem( dirname( __FILE__ ) . '/__test_files' );

			$secure_upload_config = new SecureUPloadConfig( array(
				'upload_folder' => vfsStream::url( 'temporary/uploads' ) . '/',
			) );

			self::$secureupload_obj = new SecureUPload( $secure_upload_config );

			$_FILES = array(
				'single_upload'   => array(
					'name'     => 'logo.png',
					'type'     => 'image/png',
					'tmp_name' => vfsStream::url( 'temporary/logo.png' ),
					'error'    => 0,
					'size'     => 4535
				),
				'multiple_upload' => array(
					'name'     => array(
						'logo.png',
						'logo-second.png'
					),
					'type'     => array(
						'image/png',
						'image/png'
					),
					'tmp_name' => array(
						vfsStream::url( 'temporary/logo.png' ),
						vfsStream::url( 'temporary/logo-second.png' )
					),
					'error'    => array(
						0,
						0
					),
					'size'     => array(
						4535,
						4535
					)
				)
			);
		}
	}


	public function testEmptyUploadFolderConfigException() {
		$secureupload_config = new SecureUPloadConfig;
		$this->setExpectedException( 'Alirdn\SecureUPload\Exceptions\UploadFolderException' );
		new SecureUPload( $secureupload_config );
	}

	public function testEmptyUploadFolderConfigExceptionCodeAndMessage() {
		$secureupload_config = new SecureUPloadConfig;
		try {
			new SecureUPload( $secureupload_config );
		} catch ( UploadFolderException $e ) {
			$this->assertEquals( 'Alirdn\SecureUPload Upload folder path is empty.', $e->getMessage() );
			$this->assertEquals( '1', $e->getCode() );
		}
	}

	public function testEmptyUploadFolderUploadFolderException() {
		$secureupload_config = new SecureUPloadConfig( array(
			'upload_folder' => 'foo/bar/baz'
		) );
		$this->setExpectedException( 'Alirdn\SecureUPload\Exceptions\UploadFolderException' );
		new SecureUPload( $secureupload_config );
	}

	public function testEmptyUploadFolderUploadFolderExceptionCodeAndMessage() {
		$secureupload_config = new SecureUPloadConfig( array(
			'upload_folder' => 'foo/bar/baz'
		) );
		try {
			new SecureUPload( $secureupload_config );
		} catch ( UploadFolderException $e ) {
			$this->assertEquals( 'Alirdn\SecureUPload Upload folder dose\'nt exits.', $e->getMessage() );
			$this->assertEquals( '2', $e->getCode() );
		}
	}


	public function testUploadFileMethodValidUpload() {
		$_FILES['single_upload'] = array(
			'name'     => 'logo.png',
			'type'     => 'image/png',
			'tmp_name' => vfsStream::url( 'temporary/logo.png' ),
			'error'    => 0,
			'size'     => 4535
		);

		$uploaded_file = self::$secureupload_obj->uploadFile( 'single_upload' );

		return $uploaded_file;
	}

	public function testGetUploadMethodInvalidFileId() {
		$uploaded_file = self::$secureupload_obj->getUpload( 'foo_bar' );
		$this->assertEquals( '2', $uploaded_file->status );
		$this->assertEquals( '18', $uploaded_file->error );
	}

	/**
	 * @depends testUploadFileMethodValidUpload
	 */
	public function testUploadFileMethodValidUploadCheckReturnedUpload( $uploaded_file ) {
		$this->assertInstanceOf( 'Alirdn\SecureUPload\Upload\Upload', $uploaded_file );
		$this->assertEquals( '1', $uploaded_file->status );
		$this->assertEquals( '0', $uploaded_file->error );
		$this->assertEquals( md5( 'logo.png' . 123456789 ) . '_png', $uploaded_file->id );
		$this->assertTrue( file_exists( $uploaded_file->path ) );
	}

	/**
	 * @depends testUploadFileMethodValidUploadCheckReturnedUpload
	 */
	public function testGetUploadMethodValidUploadedFileId() {
		$uploaded_file = self::$secureupload_obj->getUpload( md5( 'logo.png' . 123456789 ) . '_png' );
		$this->assertEquals( '1', $uploaded_file->status );
		$this->assertEquals( '0', $uploaded_file->error );
		$this->assertEquals( md5( 'logo.png' . 123456789 ) . '_png', $uploaded_file->id );
	}

	/**
	 * @depends testUploadFileMethodValidUploadCheckReturnedUpload
	 */
	public function testGetUploadMethodNewUploadManagerIfNotExist() {
		$secure_upload_config = new SecureUPloadConfig( array(
			'upload_folder' => vfsStream::url( 'temporary/uploads' ) . '/',
		) );

		$secureupload_obj = new SecureUPload( $secure_upload_config );
		$uploaded_file    = $secureupload_obj->getUpload( md5( 'logo.png' . 123456789 ) . '_png' );
		$this->assertEquals( '1', $uploaded_file->status );
		$this->assertEquals( '0', $uploaded_file->error );
		$this->assertEquals( md5( 'logo.png' . 123456789 ) . '_png', $uploaded_file->id );
	}


	public function testGetUploadAsFileMethodInvalidFileId() {
		$GLOBALS['_SERVER'] = array(
			'SERVER_PROTOCOL' => 'HTTP/1.1'
		);

		$secure_upload_config = new SecureUPloadConfig( array(
			'upload_folder' => vfsStream::url( 'temporary/uploads' ) . '/',
		) );

		$secureupload_obj = new SecureUPload( $secure_upload_config );

		headersClass::resetHeaders();
		$secureupload_obj->getUploadAsFile( 'foo_bar' );
		$this->assertEquals( 'HTTP/1.1 404 Not Found', headersClass::$headers[0] );
	}

	public function testGetUploadAsFileMethodValidFileId() {
		$GLOBALS['_SERVER'] = array(
			'SERVER_PROTOCOL' => 'HTTP/1.1'
		);

		$secure_upload_config = new SecureUPloadConfig( array(
			'upload_folder' => vfsStream::url( 'temporary/uploads' ) . '/',
		) );

		$secureupload_obj = new SecureUPload( $secure_upload_config );
		headersClass::resetHeaders();
		ob_start();
		$secureupload_obj->getUploadAsFile( md5( 'logo.png' . 123456789 ) . '_png' );
		$file_binary = ob_get_clean();
		$this->assertEquals( 3, count( headersClass::$headers ) );
		$this->assertEquals( 'Content-Type: image/png', headersClass::$headers[0] );
		$this->assertEquals( 'Accept-Ranges: bytes', headersClass::$headers[1] );
		$this->assertEquals( 'Content-Length: 4535', headersClass::$headers[2] );

		$this->assertEquals( $file_binary, file_get_contents( vfsStream::url( 'temporary/uploads/' . md5( 'logo.png' . 123456789 ) . '.png' ) ) );
	}

	public function testUploadFilesMethodValidUpload() {
		$_FILES['multiple_upload'] = array(
			'name'     => array(
				'logo-second.png'
			),
			'type'     => array(
				'image/png'
			),
			'tmp_name' => array(
				vfsStream::url( 'temporary/logo-second.png' )
			),
			'error'    => array(
				0
			),
			'size'     => array(
				4535
			)
		);

		$uploaded_files = self::$secureupload_obj->uploadFiles( 'multiple_upload' );

		return $uploaded_files;
	}

	/**
	 * @depends testUploadFilesMethodValidUpload
	 */
	public function testUploadFilesMethodValidUploadCheckReturnedUpload( $uploaded_files ) {
		$this->assertInstanceOf( 'Alirdn\SecureUPload\Upload\Upload', $uploaded_files[0] );
		$this->assertEquals( '1', count( $uploaded_files ) );
		$this->assertEquals( '1', $uploaded_files[0]->status );
		$this->assertEquals( '0', $uploaded_files[0]->error );
		$this->assertEquals( md5( 'logo-second.png' . 123456789 ) . '_png', $uploaded_files[0]->id );
	}

	/**
	 * @depends testUploadFileMethodValidUploadCheckReturnedUpload
	 */
	public function testUploadFilesMethodNewUploadManagerIfNotExist() {
		$secure_upload_config = new SecureUPloadConfig( array(
			'upload_folder' => vfsStream::url( 'temporary/uploads' ) . '/',
		) );

		$secureupload_obj = new SecureUPload( $secure_upload_config );
		$uploaded_file    = $secureupload_obj->uploadFiles( 'foo_bar_baz' );
		$this->assertEquals( array(), $uploaded_file );
	}

	public function testRemoveUploadMethodNonExistFileId() {
		$remove_status = self::$secureupload_obj->removeUpload( 'foo_bar_baz' );
		$this->assertFalse( $remove_status );

	}

	public function testRemoveUploadMethodExistFileId() {
		$remove_status = self::$secureupload_obj->removeUpload( md5( 'logo-second.png' . 123456789 ) . '_png' );
		$this->assertTrue( $remove_status );
		$this->assertFalse( file_exists( vfsStream::url( 'temporary/uploads/' . md5( 'logo-second.png' . 123456789 ) . '.png' ) ) );
	}

	public function testRemoveUploadMethodNewUploadManagerIfNotExist() {
		$secure_upload_config = new SecureUPloadConfig( array(
			'upload_folder' => vfsStream::url( 'temporary/uploads' ) . '/',
		) );

		$secureupload_obj = new SecureUPload( $secure_upload_config );
		$remove_status    = $secureupload_obj->removeUpload( 'foo_bar_baz' );
		$this->assertFalse( $remove_status );
	}
}