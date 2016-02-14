<?php

namespace Alirdn\SecureUPloadTest;

use Alirdn\SecureUPload\Config\SecureUPloadConfig;
use Alirdn\SecureUPload\Config\UploadConfig;
use Alirdn\SecureUPload\Upload\Upload;
use Alirdn\SecureUPload\Upload\UploadManager;
use Alirdn\SecureUPload\UploadFolder\UploadFolder;
use org\bovigo\vfs\vfsStream;

include_once dirname(dirname(__FILE__)) . '/__custom_functions/uploadmanager_class.php';
include_once dirname(dirname(__FILE__)) . '/__custom_functions/fileupload_class.php';
include_once dirname(dirname(__FILE__)) . '/__custom_functions/uploadvalidator_class.php';

class UploadManagerTest extends \PHPUnit_Framework_TestCase
{
    public static $fs;
    public static $upload_manager_organize_by_none;
    public static $upload_manager_organize_by_type;
    public static $upload_manager_organize_by_date;
    public static $upload_manager_organize_by_type_then_date;
    public static $upload_manager_organize_by_date_then_type;

    public function setUp()
    {
        if ( ! self::$upload_manager_organize_by_none) {
            self::$fs = vfsStream::setup('temporary', null, array(
                'uploads' => array()
            ));
            vfsStream::copyFromFileSystem(dirname(dirname(__FILE__)) . '/__test_files');

            $_FILES = array(
                'single_upload'   => array(
                    'name'     => '',
                    'type'     => '',
                    'tmp_name' => '',
                    'error'    => 4,
                    'size'     => 0
                ),
                'multiple_upload' => array(
                    'name'     => array(
                        '',
                        ''
                    ),
                    'type'     => array(
                        '',
                        ''
                    ),
                    'tmp_name' => array(
                        '',
                        ''
                    ),
                    'error'    => array(
                        4,
                        4
                    ),
                    'size'     => array(
                        0,
                        0
                    )
                )
            );

            $secureupload_config_organize_by_none  = new SecureUPloadConfig(
                array(
                    'upload_folder' => vfsStream::url('temporary/uploads') . '/'
                )
            );
            $upload_folder_organize_by_none        = new UploadFolder($secureupload_config_organize_by_none);
            self::$upload_manager_organize_by_none = new UploadManager($upload_folder_organize_by_none, $secureupload_config_organize_by_none);

            $secureupload_config_organize_by_type  = new SecureUPloadConfig(
                array(
                    'upload_folder' => vfsStream::url('temporary/uploads') . '/',
                    'organize_by'   => 'type'
                )
            );
            $upload_folder_organize_by_type        = new UploadFolder($secureupload_config_organize_by_type);
            self::$upload_manager_organize_by_type = new UploadManager($upload_folder_organize_by_type, $secureupload_config_organize_by_type);

            $secureupload_config_organize_by_date  = new SecureUPloadConfig(
                array(
                    'upload_folder' => vfsStream::url('temporary/uploads') . '/',
                    'organize_by'   => 'date'
                )
            );
            $upload_folder_organize_by_date        = new UploadFolder($secureupload_config_organize_by_date);
            self::$upload_manager_organize_by_date = new UploadManager($upload_folder_organize_by_date, $secureupload_config_organize_by_date);

            $secureupload_config_organize_by_type_then_date  = new SecureUPloadConfig(
                array(
                    'upload_folder' => vfsStream::url('temporary/uploads') . '/',
                    'organize_by'   => 'typeThenDate'
                )
            );
            $upload_folder_organize_by_type_then_date        = new UploadFolder($secureupload_config_organize_by_type_then_date);
            self::$upload_manager_organize_by_type_then_date = new UploadManager($upload_folder_organize_by_type_then_date,
                $secureupload_config_organize_by_type_then_date);

            $secureupload_config_organize_by_date_then_type  = new SecureUPloadConfig(
                array(
                    'upload_folder' => vfsStream::url('temporary/uploads') . '/',
                    'organize_by'   => 'dateThenType'
                )
            );
            $upload_folder_organize_by_date_then_type        = new UploadFolder($secureupload_config_organize_by_date_then_type);
            self::$upload_manager_organize_by_date_then_type = new UploadManager($upload_folder_organize_by_date_then_type,
                $secureupload_config_organize_by_date_then_type);
        }
    }

    private function restore_logo_upload()
    {
        $_FILES = array(
            'single_upload'   => array(
                'name'     => 'logo.png',
                'type'     => 'image/png',
                'tmp_name' => vfsStream::url('temporary/logo.png'),
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
                    vfsStream::url('temporary/logo.png'),
                    vfsStream::url('temporary/logo-second.png')
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
        copy(
            vfsStream::url('temporary/uploads/' . md5('logo.png' . 123456789) . '.png'),
            vfsStream::url('temporary/logo.png')
        );
    }

    public function testUploadFileMethodExpectReturnUploadObject()
    {
        $this->assertInstanceOf('Alirdn\SecureUPload\Upload\Upload', self::$upload_manager_organize_by_none->uploadFile('foo'));
    }

    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigGiveMultipleUploads()
    {
        $_FILES['multiple_upload'] = array(
            'name'     => array(
                'logo.png',
                'logo-second.png'
            ),
            'type'     => array(
                'image/png',
                'image/png'
            ),
            'tmp_name' => array(
                vfsStream::url('temporary/logo.png'),
                vfsStream::url('temporary/logo-second.png')
            ),
            'error'    => array(
                0,
                0
            ),
            'size'     => array(
                4535,
                4535
            )
        );
        $uploaded_file             = self::$upload_manager_organize_by_none->uploadFile('multiple_upload');
        $this->assertEquals('0', $uploaded_file->status);
    }

    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone()
    {
        $_FILES ['single_upload'] = array(
            'name'     => 'logo.png',
            'type'     => 'image/png',
            'tmp_name' => vfsStream::url('temporary/logo.png'),
            'error'    => 0,
            'size'     => 4535

        );
        $uploaded_file            = self::$upload_manager_organize_by_none->uploadFile('single_upload');
        //print_r($Uploaded_file);
        //vfsStream::inspect(new vfsStreamPrintVisitor());
        return $uploaded_file;
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNoneStatus($uploaded_file)
    {
        $this->assertEquals('1', $uploaded_file->status);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNoneError($uploaded_file)
    {
        $this->assertEquals('0', $uploaded_file->error);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNoneName($uploaded_file)
    {
        $this->assertEquals(md5('logo.png' . 123456789), $uploaded_file->name);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNoneExt($uploaded_file)
    {
        $this->assertEquals('png', $uploaded_file->ext);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNoneId($uploaded_file)
    {
        $this->assertEquals(md5('logo.png' . 123456789) . '_png', $uploaded_file->id);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNoneRelativePath($uploaded_file)
    {
        $this->assertEquals(md5('logo.png' . 123456789) . '.png', $uploaded_file->relative_path);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNoneRelativeUrl($uploaded_file)
    {
        $this->assertEquals(md5('logo.png' . 123456789) . '.png', $uploaded_file->relative_url);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNonePath($uploaded_file)
    {
        $this->assertEquals(vfsStream::url('temporary/uploads/' . md5('logo.png' . 123456789) . '.png'), $uploaded_file->path);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNoneSize($uploaded_file)
    {
        $this->assertEquals(4535, $uploaded_file->size);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNoneType($uploaded_file)
    {
        $this->assertEquals('image/png', $uploaded_file->type);
    }

    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByType()
    {
        $this->restore_logo_upload();

        $uploaded_file = self::$upload_manager_organize_by_type->uploadFile('single_upload');
        //print_r($Uploaded_file);
        //vfsStream::inspect(new vfsStreamPrintVisitor());
        return $uploaded_file;
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByType
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByTypeCheckId($uploaded_file)
    {
        $this->assertEquals('png_' . md5('logo.png' . 123456789) . '_png', $uploaded_file->id);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByType
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByTypeCheckPathCreated()
    {
        $this->assertTrue(self::$fs->hasChild('temporary/uploads/png'));
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByType
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByTypeCheckUploadMoved()
    {
        $this->assertTrue(self::$fs->hasChild('temporary/uploads/png/' . md5('logo.png' . 123456789) . '.png'));
    }

    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByDate()
    {
        $this->restore_logo_upload();

        $uploaded_file = self::$upload_manager_organize_by_date->uploadFile('single_upload');

        return $uploaded_file;
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByDate
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByDateCheckId($uploaded_file)
    {
        $this->assertEquals('y_m_d_' . md5('logo.png' . 123456789) . '_png', $uploaded_file->id);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByDate
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByDateCheckPathCreated()
    {
        $this->assertTrue(self::$fs->hasChild('temporary/uploads/y/m/d'));
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByDate
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByDateCheckUploadMoved()
    {
        $this->assertTrue(self::$fs->hasChild('temporary/uploads/y/m/d/' . md5('logo.png' . 123456789) . '.png'));
    }

    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByTypeThenDate()
    {
        $this->restore_logo_upload();

        $uploaded_file = self::$upload_manager_organize_by_type_then_date->uploadFile('single_upload');

        return $uploaded_file;
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByTypeThenDate
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByTypeThenDateCheckId($uploaded_file)
    {
        $this->assertEquals('png_y_m_d_' . md5('logo.png' . 123456789) . '_png', $uploaded_file->id);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByTypeThenDate
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByTypeThenDateCheckPathCreated()
    {
        $this->assertTrue(self::$fs->hasChild('temporary/uploads/png/y/m/d'));
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByTypeThenDate
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByTypeThenDateCheckUploadMoved()
    {
        $this->assertTrue(self::$fs->hasChild('temporary/uploads/png/y/m/d/' . md5('logo.png' . 123456789) . '.png'));
    }

    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByDateThenType()
    {
        $this->restore_logo_upload();

        $uploaded_file = self::$upload_manager_organize_by_date_then_type->uploadFile('single_upload');

        return $uploaded_file;
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByDateThenType
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByDateThenTypeCheckId($uploaded_file)
    {
        $this->assertEquals('y_m_d_png_' . md5('logo.png' . 123456789) . '_png', $uploaded_file->id);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByDateThenType
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByDateThenTypeCheckPathCreated()
    {
        $this->assertTrue(self::$fs->hasChild('temporary/uploads/y/m/d/png'));
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByDateThenType
     */
    public function testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByDateThenTypeCheckUploadMoved()
    {
        $this->assertTrue(self::$fs->hasChild('temporary/uploads/y/m/d/png/' . md5('logo.png' . 123456789) . '.png'));
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testRemoveUploadMethodNonExistUpload()
    {
        $this->assertFalse(self::$upload_manager_organize_by_none->removeUpload('bar_baz'));
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testRemoveUploadMethodExistUpload()
    {
        $this->assertTrue(self::$upload_manager_organize_by_none->removeUpload('y_m_d_png_' . md5('logo.png' . 123456789) . '_png'));
    }

    /**
     * @depends testRemoveUploadMethodExistUpload
     */
    public function testRemoveUploadMethodExistUploadCheckFileRemoved()
    {
        $this->assertFalse(self::$fs->hasChild('temporary/uploads/y/m/d/png/' . md5('logo.png' . 123456789) . '.png'));
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testGetUploadMethodNonExistUpload()
    {
        $uploaded_file_by_id = self::$upload_manager_organize_by_none->getUpload('bar_baz');

        return $uploaded_file_by_id;
    }

    /**
     * @depends testGetUploadMethodNonExistUpload
     */
    public function testGetUploadMethodNonExistUploadCheckStatus($uploaded_file_by_id)
    {
        $this->assertEquals('2', $uploaded_file_by_id->status);
    }

    /**
     * @depends testGetUploadMethodNonExistUpload
     */
    public function testGetUploadMethodNonExistUploadCheckError($uploaded_file_by_id)
    {
        $this->assertEquals('18', $uploaded_file_by_id->error);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testGetUploadMethodExistUpload()
    {
        $uploaded_file_by_id = self::$upload_manager_organize_by_none->getUpload('17b9a960a38d2c08c6c56c097446d1bf_png');

        return $uploaded_file_by_id;
    }

    /**
     * @depends testGetUploadMethodExistUpload
     */
    public function testGetUploadMethodExistUploadCheckStatus($uploaded_file_by_id)
    {
        $this->assertEquals('1', $uploaded_file_by_id->status);
    }

    /**
     * @depends testGetUploadMethodExistUpload
     */
    public function testGetUploadMethodExistUploadCheckError($uploaded_file_by_id)
    {
        $this->assertEquals('0', $uploaded_file_by_id->error);
    }

    /**
     * @depends testGetUploadMethodExistUpload
     */
    public function testGetUploadMethodExistUploadCheckPath($uploaded_file_by_id)
    {
        $this->assertEquals(vfsStream::url('temporary/uploads/' . md5('logo.png' . 123456789) . '.png'), $uploaded_file_by_id->path);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testCheckUploadTmpInfoMethodByUploadWithErrorInUploadTmpInfo()
    {
        $_FILES['simple_upload'] = array(
            'name'     => 'foo.bar',
            'type'     => 'foo/bar',
            'tmp_name' => vfsStream::url('temporary/foo.bar'),
            'error'    => 8,
            'size'     => 0
        );

        $uploaded_file_by_id = self::$upload_manager_organize_by_none->uploadFile('simple_upload');

        return $uploaded_file_by_id;
    }

    /**
     * @depends testCheckUploadTmpInfoMethodByUploadWithErrorInUploadTmpInfo
     */
    public function testCheckUploadTmpInfoMethodByUploadWithErrorInUploadTmpInfoCheckStatus($uploaded_file_by_id)
    {
        $this->assertEquals('2', $uploaded_file_by_id->status);
    }

    /**
     * @depends testCheckUploadTmpInfoMethodByUploadWithErrorInUploadTmpInfo
     */
    public function testCheckUploadTmpInfoMethodByUploadWithErrorInUploadTmpInfoCheckError($uploaded_file_by_id)
    {
        $this->assertEquals('8', $uploaded_file_by_id->error);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testCheckUploadTmpInfoMethodByUploadWithForbiddenFile()
    {
        $_FILES['simple_upload'] = array(
            'name'     => 'forbidden.php',
            'type'     => 'text/php',
            'tmp_name' => vfsStream::url('temporary/forbidden.php'),
            'error'    => 0,
            'size'     => 0
        );

        $uploaded_file_by_id = self::$upload_manager_organize_by_none->uploadFile('simple_upload');

        return $uploaded_file_by_id;
    }

    /**
     * @depends testCheckUploadTmpInfoMethodByUploadWithForbiddenFile
     */
    public function testCheckUploadTmpInfoMethodByUploadWithForbiddenFileCheckStatus($uploaded_file_by_id)
    {
        $this->assertEquals('2', $uploaded_file_by_id->status);
    }

    /**
     * @depends testCheckUploadTmpInfoMethodByUploadWithForbiddenFile
     */
    public function testCheckUploadTmpInfoMethodByUploadWithForbiddenFileCheckError($uploaded_file_by_id)
    {
        $this->assertEquals('14', $uploaded_file_by_id->error);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testSaveUploadMethodAndMakeMoveUploadedFileGiveError()
    {
        $reflectionClass    = new \ReflectionClass(self::$upload_manager_organize_by_none);
        $save_upload_method = $reflectionClass->getMethod('saveUpload');
        $save_upload_method->setAccessible(true);

        $upload = new Upload();

        $save_upload_method->invokeArgs(self::$upload_manager_organize_by_none, array($upload));

        return $upload;
    }

    /**
     * @depends testSaveUploadMethodAndMakeMoveUploadedFileGiveError
     */
    public function testSaveUploadMethodAndMakeMoveUploadedFileGiveErrorCheckStatus($uploaded_file_by_id)
    {
        $this->assertEquals('2', $uploaded_file_by_id->status);
    }

    /**
     * @depends testSaveUploadMethodAndMakeMoveUploadedFileGiveError
     */
    public function testSaveUploadMethodAndMakeMoveUploadedFileGiveErrorCheckError($uploaded_file_by_id)
    {
        $this->assertEquals('17', $uploaded_file_by_id->error);
    }

    /**
     * @depends testUploadFileMethodSuccessUploadBySecureUPloadConfigOrganizeByNone
     */
    public function testUploadFileMethodValidateByUploadConfig()
    {
        $this->restore_logo_upload();

        $upload_config = new UploadConfig(array(
            'file_types' => array('jpg' => 'image/jpeg')
        ));

        $uploaded_file = self::$upload_manager_organize_by_none->uploadFile('single_upload', $upload_config);


        return $uploaded_file;
    }

    /**
     * @depends testUploadFileMethodValidateByUploadConfig
     */
    public function testUploadFileMethodValidateByUploadConfigCheckUploadError($uploaded_file)
    {
        $this->assertEquals('15', $uploaded_file->error);
    }

    public function testUploadFileSMethodNonExistFilesId()
    {
        $uploaded_files = self::$upload_manager_organize_by_none->uploadFiles('foo_bar');

        return $uploaded_files;
    }

    /**
     * @depends testUploadFileSMethodNonExistFilesId
     */
    public function testUploadFileSMethodNonExistFilesIdCheckReturnedUploads($uploaded_files)
    {
        $this->assertEquals(array(), $uploaded_files);
        $this->assertEquals(0, count($uploaded_files));
    }

    public function testUploadFileSMethodSingleFilesIndex()
    {
        $_FILES['single_upload'] = array(
            'name'     => 'foo.bar',
            'type'     => 'foo/bar',
            'tmp_name' => vfsStream::url('temporary/foo.bar'),
            'error'    => 0,
            'size'     => 0
        );
        $uploaded_files          = self::$upload_manager_organize_by_none->uploadFiles('single_upload');

        return $uploaded_files;
    }

    /**
     * @depends testUploadFileSMethodSingleFilesIndex
     */
    public function testUploadFileSMethodSingleFilesIndexCheckReturnedValue($uploaded_files)
    {
        $this->assertEquals(array(), $uploaded_files);
        $this->assertEquals('0', count($uploaded_files));
    }

    public function testUploadFileSMethodExistFilesIdInvalidUploads()
    {
        /*
        $_FILES['multiple_upload'] = array(
            'name'     => array(
                'logo.png',
                'logo-second.png'
            ),
            'type'     => array(
                'image/png',
                'image/png'
            ),
            'tmp_name' => array(
                vfsStream::url('temporary/logo.png'),
                vfsStream::url('temporary/logo-second.png')
            ),
            'error'    => array(
                8,
                8
            ),
            'size'     => array(
                4535,
                4535
            )
        );*/

        $_FILES['multiple_upload'] = array(
            'name'     => array(
                'foo.bar',
                'baz.qux'
            ),
            'type'     => array(
                'foo/bar',
                'baz/qux'
            ),
            'tmp_name' => array(
                vfsStream::url('temporary/foo.bar'),
                vfsStream::url('temporary/baz.qux')
            ),
            'error'    => array(
                8,
                8
            ),
            'size'     => array(
                0,
                0
            )
        );
        $uploaded_files            = self::$upload_manager_organize_by_none->uploadFiles('multiple_upload');

        return $uploaded_files;
    }

    /**
     * @depends testUploadFileSMethodExistFilesIdInvalidUploads
     */
    public function testUploadFileSMethodExistFilesIdInvalidUploadsCheckErrors($uploaded_files)
    {
        $this->assertEquals('2', count($uploaded_files));
        $this->assertEquals('2', $uploaded_files[0]->status);
        $this->assertEquals('8', $uploaded_files[0]->error);
        $this->assertEquals('2', $uploaded_files[1]->status);
        $this->assertEquals('8', $uploaded_files[1]->error);
    }

    public function testUploadFileSMethodExistFilesIdValidUploads()
    {

        $_FILES['multiple_upload'] = array(
            'name'     => array(
                'logo.png',
                'logo-second.png'
            ),
            'type'     => array(
                'image/png',
                'image/png'
            ),
            'tmp_name' => array(
                vfsStream::url('temporary/logo.png'),
                vfsStream::url('temporary/logo-second.png')
            ),
            'error'    => array(
                0,
                0
            ),
            'size'     => array(
                4535,
                4535
            )
        );
        $uploaded_files            = self::$upload_manager_organize_by_none->uploadFiles('multiple_upload');

        return $uploaded_files;
    }

    /**
     * @depends testUploadFileSMethodExistFilesIdValidUploads
     */
    public function testUploadFileSMethodExistFilesIdValidUploadsCheckReturnedUploads($uploaded_files)
    {

        $this->assertEquals('2', count($uploaded_files));
        $this->assertEquals('1', $uploaded_files[0]->status);
        $this->assertEquals('0', $uploaded_files[0]->error);
        $this->assertEquals(md5('logo.png' . 123456789) . '_png', $uploaded_files[0]->id);
        $this->assertEquals('1', $uploaded_files[1]->status);
        $this->assertEquals('0', $uploaded_files[1]->error);
        $this->assertEquals(md5('logo-second.png' . 123456789) . '_png', $uploaded_files[1]->id);
    }

}