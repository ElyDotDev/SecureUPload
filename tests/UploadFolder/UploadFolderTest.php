<?php
namespace Alirdn\SecureUPloadTest;

use Alirdn\SecureUPload\Config\SecureUPloadConfig;
use Alirdn\SecureUPload\UploadFolder\Htaccess;
use Alirdn\SecureUPload\UploadFolder\UploadFolder;
use org\bovigo\vfs\vfsStream;

include_once dirname(dirname(__FILE__)) . '/__custom_functions/fileupload_class.php';

class UploadFolderTest extends \PHPUnit_Framework_TestCase
{

    public static $fs;
    public static $upload_folder_storage_type_1_object;
    public static $upload_folder_storage_type_2_object;
    public static $upload_folder_storage_type_3_object;
    public static $upload_folder_changeable_object;

    public function setUp()
    {
        if ( ! self::$fs) {
            self::$fs = vfsStream::setup('temporary', null, array(
                'organize_by_1' => array(),
                'organize_by_2' => array(),
                'organize_by_3' => array(),
                'changeable'    => array()
            ));
        }

        if ( ! self::$upload_folder_storage_type_1_object) {
            $upload_folder_storage_type_1_secureupload_config = new SecureUPloadConfig(
                array(
                    'upload_folder' => vfsStream::url('temporary/organize_by_1') . '/',
                    'storage_type'  => '1',
                    'file_types'    => array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg')
                )
            );
            self::$upload_folder_storage_type_1_object        = new UploadFolder($upload_folder_storage_type_1_secureupload_config);
            self::$upload_folder_storage_type_1_object->init();
        }

        if ( ! self::$upload_folder_storage_type_2_object) {
            $upload_folder_storage_type_2_secureupload_config = new SecureUPloadConfig(
                array(
                    'upload_folder' => vfsStream::url('temporary/organize_by_2') . '/',
                    'storage_type'  => '2'
                )
            );
            self::$upload_folder_storage_type_2_object        = new UploadFolder($upload_folder_storage_type_2_secureupload_config);
            self::$upload_folder_storage_type_2_object->init();
        }

        if ( ! self::$upload_folder_storage_type_3_object) {
            $upload_folder_storage_type_3_secureupload_config = new SecureUPloadConfig(
                array(
                    'upload_folder' => vfsStream::url('temporary/organize_by_3') . '/',
                    'storage_type'  => '3'
                )
            );
            self::$upload_folder_storage_type_3_object        = new UploadFolder($upload_folder_storage_type_3_secureupload_config);
            self::$upload_folder_storage_type_3_object->init();
        }

        if ( ! self::$upload_folder_changeable_object) {
            $upload_folder_storage_changeable_secureupload_config = new SecureUPloadConfig(
                array(
                    'upload_folder' => vfsStream::url('temporary/changeable') . '/',
                    'storage_type'  => '1',
                    'file_types'    => array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg')
                )
            );
            self::$upload_folder_changeable_object                = new UploadFolder($upload_folder_storage_changeable_secureupload_config);
            self::$upload_folder_changeable_object->init();
        }
        //vfsStream::inspect(new vfsStreamPrintVisitor());
    }

    public function testNonExistUploadFolderExistMethod()
    {
        $non_exist_upload_folder_secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('foo/bar/baz')
            )
        );

        $non_exist_upload_folder = new UploadFolder($non_exist_upload_folder_secureupload_config);

        $this->assertFalse($non_exist_upload_folder->exist());
    }


    public function testExistUploadFolderExistMethod()
    {
        $this->assertTrue(self::$upload_folder_storage_type_1_object->exist());
    }

    /* Storage Type 1 */
    public function testUploadFolderStorageType1Exist()
    {
        $this->assertTrue(self::$fs->hasChild('organize_by_1'));
    }

    /**
     * @depends testUploadFolderStorageType1Exist
     */
    public function testUploadFolderStorageType1HasHtaccess()
    {
        $this->assertTrue(self::$fs->hasChild('organize_by_1/.htaccess'));
    }

    /**
     * @depends testUploadFolderStorageType1HasHtaccess
     */
    public function testUploadFolderStorageType1HtaccessContent()
    {
        $saved_htaccess_content = self::$fs->getChild('organize_by_1/.htaccess')->getContent();
        $htaccess               = new Htaccess();
        $htaccess->setFileTypes('jpg|jpeg');
        $this->assertEquals($htaccess->getContent(), $saved_htaccess_content);
    }

    /**
     * @depends testUploadFolderStorageType1Exist
     */
    public function testUploadFolderStorageType1HasHtaccessChecksum()
    {
        $this->assertTrue(self::$fs->hasChild('organize_by_1/.htaccess_checksum'));
    }

    /**
     * @depends testUploadFolderStorageType1HasHtaccess
     * @depends testUploadFolderStorageType1HasHtaccessChecksum
     */
    public function testUploadFolderStorageType1HtaccessChecksumEqualsToSavedChecksum()
    {
        $htaccess_checksum = sha1_file(vfsStream::url('temporary/organize_by_1/.htaccess'));
        $saved_checksum    = self::$fs->getChild('organize_by_1/.htaccess_checksum')->getContent();
        $this->assertEquals($saved_checksum, $htaccess_checksum);
    }

    /**
     * @depends testUploadFolderStorageType1Exist
     */
    public function testUploadFolderOrStorageType1HasInfoFolder()
    {
        $this->assertTrue(self::$fs->hasChild('organize_by_1/.info'));
    }

    /**
     * @depends testUploadFolderOrStorageType1HasInfoFolder
     */
    public function testUploadFolderOrStorageType1InfoFolderHasStorageTypeFile()
    {
        $this->assertTrue(self::$fs->hasChild('organize_by_1/.info/storage_type.txt'));
    }

    /**
     * @depends testUploadFolderOrStorageType1InfoFolderHasStorageTypeFile
     */
    public function testUploadFolderStorageType1InfoFolderStorageTypeEqualsTo1()
    {
        $this->assertEquals('1', self::$fs->getChild('organize_by_1/.info/storage_type.txt')->getContent());
    }

    /**
     * @depends testUploadFolderOrStorageType1HasInfoFolder
     */
    public function testUploadFolderStorageType1InfoFolderHasFileTypesFile()
    {
        $this->assertTrue(self::$fs->hasChild('organize_by_1/.info/file_types.txt'));
    }

    /**
     * @depends testUploadFolderStorageType1InfoFolderHasFileTypesFile
     */
    public function testUploadFolderStorageType1InfoFolderFileTypesContent()
    {
        $this->assertEquals('jpg|jpeg', self::$fs->getChild('organize_by_1/.info/file_types.txt')->getContent());
    }

    /* Storage Type 2 */

    public function testUploadFolderStorageType2Exist()
    {
        $this->assertTrue(self::$fs->hasChild('organize_by_2'));
    }

    /**
     * @depends testUploadFolderStorageType2Exist
     */
    public function testUploadFolderStorageType2HasHtaccess()
    {
        $this->assertTrue(self::$fs->hasChild('organize_by_2/.htaccess'));
    }

    /**
     * @depends testUploadFolderStorageType2HasHtaccess
     */
    public function testUploadFolderStorageType2HtaccessContent()
    {
        $saved_htaccess_content = self::$fs->getChild('organize_by_2/.htaccess')->getContent();
        $htaccess               = new Htaccess();
        $this->assertEquals($htaccess->getContent(), $saved_htaccess_content);
    }

    /**
     * @depends testUploadFolderStorageType2Exist
     */
    public function testUploadFolderStorageType2HasHtaccessChecksum()
    {
        $this->assertTrue(self::$fs->hasChild('organize_by_2/.htaccess_checksum'));
    }

    /**
     * @depends testUploadFolderStorageType2HasHtaccess
     * @depends testUploadFolderStorageType2HasHtaccessChecksum
     */
    public function testUploadFolderStorageType2HtaccessChecksumEqualsToSavedChecksum()
    {
        $htaccess_checksum = sha1_file(vfsStream::url('temporary/organize_by_2/.htaccess'));
        $saved_checksum    = self::$fs->getChild('organize_by_2/.htaccess_checksum')->getContent();
        $this->assertEquals($saved_checksum, $htaccess_checksum);
    }

    /**
     * @depends testUploadFolderStorageType2Exist
     */
    public function testUploadFolderOrStorageType2HasInfoFolder()
    {
        $this->assertTrue(self::$fs->hasChild('organize_by_2/.info'));
    }

    /**
     * @depends testUploadFolderOrStorageType2HasInfoFolder
     */
    public function testUploadFolderOrStorageType2InfoFolderHasStorageTypeFile()
    {
        $this->assertTrue(self::$fs->hasChild('organize_by_2/.info/storage_type.txt'));
    }

    /**
     * @depends testUploadFolderOrStorageType2InfoFolderHasStorageTypeFile
     */
    public function testUploadFolderStorageType2InfoFolderStorageTypeEqualsTo2()
    {
        $this->assertEquals('2', self::$fs->getChild('organize_by_2/.info/storage_type.txt')->getContent());
    }

    /**
     * @depends testUploadFolderStorageType2HasHtaccess
     */
    public function testUploadFolderStorageType2InfoFolderHasNoFileTypesFile()
    {
        $this->assertFalse(self::$fs->hasChild('organize_by_2/.info/file_types.txt'));
    }

    /* Storage Type 3 */
    public function testUploadFolderStorageType3Exist()
    {
        $this->assertTrue(self::$fs->hasChild('organize_by_3'));
    }

    /**
     * @depends testUploadFolderStorageType3Exist
     */
    public function testUploadFolderStorageType3HasNoHtaccess()
    {
        $this->assertFalse(self::$fs->hasChild('organize_by_3/.htaccess'));
    }

    /**
     * @depends testUploadFolderStorageType3Exist
     */
    public function testUploadFolderOrganizeBy3HasNoHtaccessChecksum()
    {
        $this->assertFalse(self::$fs->hasChild('organize_by_3/.htaccess_checksum'));
    }

    /**
     * @depends testUploadFolderStorageType3Exist
     */
    public function testUploadFolderOrStorageType3HasInfoFolder()
    {
        $this->assertTrue(self::$fs->hasChild('organize_by_3/.info'));
    }

    /**
     * @depends testUploadFolderOrStorageType3HasInfoFolder
     */
    public function testUploadFolderOrStorageType3InfoFolderHasStorageTypeFile()
    {
        $this->assertTrue(self::$fs->hasChild('organize_by_3/.info/storage_type.txt'));
    }

    /**
     * @depends testUploadFolderOrStorageType3InfoFolderHasStorageTypeFile
     */
    public function testUploadFolderStorageType3InfoFolderStorageTypeEqualsTo3()
    {
        $this->assertEquals('3', self::$fs->getChild('organize_by_3/.info/storage_type.txt')->getContent());
    }

    /**
     * @depends testUploadFolderOrStorageType3HasInfoFolder
     */
    public function testUploadFolderStorageType3InfoFolderHasNoFileTypesFile()
    {
        $this->assertFalse(self::$fs->hasChild('organize_by_3/.info/file_types.txt'));
    }

    /*
     * Changeable Test
     * */

    public function testUploadFolderChangeableStorage1ToStorage1WithoutNewFileTypes()
    {
        $changeable_secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary/changeable') . '/',
                'storage_type'  => '1',
                'file_types'    => array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg')
            )
        );
        $changeable_upload_folder       = new UploadFolder($changeable_secureupload_config);
        $changeable_upload_folder->init();
    }

    /**
     * @depends testUploadFolderChangeableStorage1ToStorage1WithoutNewFileTypes
     */
    public function testUploadFolderChangeableStorage1ToStorage1FileTypesFile()
    {
        $this->assertEquals('jpg|jpeg', self::$fs->getChild('changeable/.info/file_types.txt')->getContent());
    }

    /**
     * @depends testUploadFolderChangeableStorage1ToStorage1FileTypesFile
     */
    public function testUploadFolderChangeableStorage1ToStorage1WithNewFileTypes()
    {
        $changeable_secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary/changeable') . '/',
                'storage_type'  => '1',
                'file_types'    => array('png' => 'image/png', 'gif' => 'image/gif')
            )
        );
        $changeable_upload_folder       = new UploadFolder($changeable_secureupload_config);
        $changeable_upload_folder->init();
    }

    /**
     * @depends testUploadFolderChangeableStorage1ToStorage1WithNewFileTypes
     */
    public function testUploadFolderChangeableToStorage1ChangedFileTypesFileTypesFile()
    {
        $this->assertEquals('png|gif', self::$fs->getChild('changeable/.info/file_types.txt')->getContent());
    }

    /**
     * @depends testUploadFolderChangeableStorage1ToStorage1WithNewFileTypes
     */
    public function testUploadFolderChangeableStorage1ToStorage1ChangedFileTypesHtaccessFile()
    {
        $saved_htaccess_content = self::$fs->getChild('changeable/.htaccess')->getContent();
        $htaccess               = new Htaccess();
        $htaccess->setFileTypes('png|gif');
        $this->assertEquals($htaccess->getContent(), $saved_htaccess_content);
    }

    /**
     * @depends testUploadFolderChangeableStorage1ToStorage1ChangedFileTypesHtaccessFile
     */
    public function testUploadFolderChangeableStorage1ToStorage2()
    {
        $changeable_secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary/changeable') . '/',
                'storage_type'  => '2'
            )
        );
        $changeable_upload_folder       = new UploadFolder($changeable_secureupload_config);
        $changeable_upload_folder->init();
    }

    /**
     * @depends testUploadFolderChangeableStorage1ToStorage2
     */
    public function testUploadFolderChangeableStorage1ToStorage2HasHtaccess()
    {
        $this->assertTrue(self::$fs->hasChild('changeable/.htaccess'));
    }

    /**
     * @depends testUploadFolderChangeableStorage1ToStorage2HasHtaccess
     */
    public function testUploadFolderChangeableStorage1ToStorage2HtaccessFile()
    {
        $saved_htaccess_content = self::$fs->getChild('changeable/.htaccess')->getContent();
        $htaccess               = new Htaccess();
        $this->assertEquals($htaccess->getContent(), $saved_htaccess_content);
    }

    /**
     * @depends testUploadFolderChangeableStorage1ToStorage2HasHtaccess
     */
    public function testUploadFolderChangeableStorage1ToStorage2HasHtaccessChecksumFile()
    {
        $this->assertTrue(self::$fs->hasChild('changeable/.htaccess_checksum'));
    }

    /**
     * @depends testUploadFolderChangeableStorage1ToStorage2HasHtaccessChecksumFile
     */
    public function testUploadFolderChangeableStorage1ToStorage2HtaccessChecksumEqualsToSavedChecksum()
    {
        $htaccess_checksum = sha1_file(vfsStream::url('temporary/changeable/.htaccess'));
        $saved_checksum    = self::$fs->getChild('changeable/.htaccess_checksum')->getContent();
        $this->assertEquals($saved_checksum, $htaccess_checksum);
    }

    /**
     * @depends testUploadFolderChangeableStorage1ToStorage2HtaccessChecksumEqualsToSavedChecksum
     */
    public function testUploadFolderChangeableStorage1ToStorage2SavedStorageTypeFile()
    {
        $this->assertEquals('2', self::$fs->getChild('changeable/.info/storage_type.txt')->getContent());
    }

    /**
     * @depends testUploadFolderChangeableStorage1ToStorage2SavedStorageTypeFile
     */
    public function testUploadFolderChangeableStorage1ToStorage2NoFileTypesFile()
    {
        $this->assertFalse(self::$fs->hasChild('changeable/.info/file_types.txt'));
    }

    /**
     * @depends testUploadFolderChangeableStorage1ToStorage2NoFileTypesFile
     */
    public function testUploadFolderChangeableStorage2ToStorage1ThenTo3()
    {
        $changeable_secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary/changeable') . '/',
                'storage_type'  => '1',
                'file_types'    => array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg')
            )
        );
        $changeable_upload_folder       = new UploadFolder($changeable_secureupload_config);
        $changeable_upload_folder->init();

        $changeable_secureupload_config = new SecureUPloadConfig(
            array(
                'upload_folder' => vfsStream::url('temporary/changeable') . '/',
                'storage_type'  => '3'
            )
        );
        $changeable_upload_folder       = new UploadFolder($changeable_secureupload_config);
        $changeable_upload_folder->init();
    }

    /**
     * @depends testUploadFolderChangeableStorage2ToStorage1ThenTo3
     */
    public function testUploadFolderChangeableStorage2ToStorage1ThenTo3HasNoHtaccess()
    {
        $this->assertFalse(self::$fs->hasChild('changeable/.htaccess'));
    }

    /**
     * @depends testUploadFolderChangeableStorage2ToStorage1ThenTo3HasNoHtaccess
     */
    public function testUploadFolderChangeableStorage2ToStorage1ThenTo3HasNoHtaccessChecksum()
    {
        $this->assertFalse(self::$fs->hasChild('changeable/.htaccess_checksum'));
    }

    /**
     * @depends testUploadFolderChangeableStorage2ToStorage1ThenTo3HasNoHtaccessChecksum
     */
    public function testUploadFolderChangeableStorage2ToStorage1ThenTo3HasInfoFolder()
    {
        $this->assertTrue(self::$fs->hasChild('changeable/.info'));
    }

    /**
     * @depends testUploadFolderChangeableStorage2ToStorage1ThenTo3HasInfoFolder
     */
    public function testUploadFolderChangeableStorage2ToStorage1ThenTo3InfoFolderHasStorageTypeFile()
    {
        $this->assertTrue(self::$fs->hasChild('changeable/.info/storage_type.txt'));
    }

    /**
     * @depends testUploadFolderChangeableStorage2ToStorage1ThenTo3InfoFolderHasStorageTypeFile
     */
    public function testUploadFolderChangeableStorage2ToStorage1ThenTo3InfoFolderStorageTypeEqualsTo3()
    {
        $this->assertEquals('3', self::$fs->getChild('changeable/.info/storage_type.txt')->getContent());
    }

    /**
     * @depends testUploadFolderChangeableStorage2ToStorage1ThenTo3InfoFolderStorageTypeEqualsTo3
     */
    public function testUploadFolderChangeableStorage2ToStorage1ThenTo3InfoFolderHasNoFileTypesFile()
    {
        $this->assertFalse(self::$fs->hasChild('changeable/.info/file_types.txt'));
    }

    /**
     * @depends testUploadFolderChangeableStorage2ToStorage1ThenTo3InfoFolderHasNoFileTypesFile
     */
    public function testUploadFolderStorageType1MoveUploadedFileNonExistFileMethod()
    {
        $path        = array('foo', 'bar', 'baz');
        $path_string = implode(DIRECTORY_SEPARATOR, $path);

        $this->assertFalse(
            self::$upload_folder_changeable_object->moveUploadedFile($path, $path_string, vfsStream::url('temporary/qux.quux'), 'corge', 'quux')
        );
    }

    /**
     * @depends testUploadFolderStorageType1MoveUploadedFileNonExistFileMethod
     */
    public function testUploadFolderStorageType1MoveUploadedFileCreatedPathCheck()
    {
        $this->assertTrue(self::$fs->hasChild('changeable/foo/bar/baz'));
    }

    /**
     * @depends testUploadFolderStorageType1MoveUploadedFileCreatedPathCheck
     */
    public function testUploadFolderStorageType1MoveUploadedFileExistFileMethod()
    {
        $path        = array('foo', 'bar', 'baz');
        $path_string = implode(DIRECTORY_SEPARATOR, $path) . DIRECTORY_SEPARATOR;

        vfsStream::newFile('qux.quux')->at(self::$fs)->setContent('TESTFILECONTENT');

        $this->assertTrue(
            self::$upload_folder_changeable_object->moveUploadedFile($path, $path_string, vfsStream::url('temporary/qux.quux'), 'corge', 'quux')
        );
    }

    /**
     * @depends testUploadFolderStorageType1MoveUploadedFileExistFileMethod
     */
    public function testUploadFolderStorageType1MoveUploadedFileExistFileCheckMovedFile()
    {
        $this->assertFalse(self::$fs->hasChild('temporary/corge.quux'));
        $this->assertTrue(self::$fs->hasChild('changeable/foo/bar/baz/corge.quux'));
    }

}