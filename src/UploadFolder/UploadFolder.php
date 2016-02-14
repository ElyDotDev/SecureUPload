<?php
/**
 * This file is part of the SecureUPload package.
 *
 * (c) Alireza Dabiri Nejad <me@allii.ir>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alirdn\SecureUPload\UploadFolder;

use Alirdn\SecureUPload\Config\SecureUPloadConfig;
use Alirdn\SecureUPload\Upload;

/**
 * Class UploadFolder
 *
 * Do all the things that are related to upload folder
 *
 * @author Alireza Dabiri Nejad <me@allii.ir>
 */
class UploadFolder
{
    /**
     * @var object SecureUPloadConfig Stores provided config.
     */
    private $SecureUPloadConfig;

    /**
     * @var bool Whether saved storage type in upload folder changed or not.
     */
    private $storage_type_changed = false;

    /**
     * @var bool Whether saved file types in upload folder changed or not.
     */
    private $file_types_changed = false;

    /**
     * UploadFolder constructor.
     *
     * @param SecureUPloadConfig $SecureUPloadConfig
     */
    public function __construct(SecureUPloadConfig $SecureUPloadConfig)
    {
        $this->SecureUPloadConfig = $SecureUPloadConfig;
    }

    /**
     * Check if upload folder path exist or not.
     *
     * @return bool upload folder exist in filesystem or not
     */
    public function exist()
    {
        return is_dir($this->SecureUPloadConfig->get('upload_folder'));
    }

    /**
     * Initialize FolderUpload
     *
     * Initialization has two step. First initialize .info folder. Second initialize .htaccess file
     *
     */
    public function init()
    {
        $upload_folder     = $this->SecureUPloadConfig->get('upload_folder');
        $storage_type      = $this->SecureUPloadConfig->get('storage_type');
        $file_types        = $this->SecureUPloadConfig->get('file_types');
        $file_types_string = implode('|', array_keys($file_types));
        $this->initInfoFolder($upload_folder, $storage_type, $file_types_string);
        $this->initHtaccess($upload_folder, $storage_type, $file_types_string);
    }

    /**
     * Initialize .info folder
     *
     * .info folder will store some information about how SecureUpload previously initialized as file.
     * Information:
     *      storage_type.txt Type of storage. Example: 1
     *      file_types.txt File types as string. Example: jpg|jpeg|png|gif
     *
     *  Note: file_types.txt is just for storage type === 1
     *
     * @param string $upload_folder     Upload folder path in SecureUPloadConfig
     * @param array  $storage_type      Storage type in SecureUPloadConfig
     * @param string $file_types_string File types in SecureUPloadConfig as a string
     */
    private function initInfoFolder($upload_folder, $storage_type, $file_types_string)
    {
        $info_folder = $upload_folder . '.info' . DIRECTORY_SEPARATOR;

        if ( ! is_dir($info_folder)) {
            mkdir($info_folder);
        }

        $this->initInfoFolderStorageType($info_folder, $storage_type);
        $this->initInfoFolderFileTypes($info_folder, $storage_type, $file_types_string);
    }

    /**
     * Initialize storage_type.txt
     *
     * Check storage_type.txt exist. If yes get it's content and check with
     * current storage type. If not create it. If it dos'ent exist or value mismatched,
     * set $storage_type_changed class property as true and write to storage_type.txt
     *
     * @param string $info_folder  Info folder path
     * @param string $storage_type Storage type
     */
    private function initInfoFolderStorageType($info_folder, $storage_type)
    {
        if (file_exists($info_folder . 'storage_type.txt')) {
            $saved_storage_type = file_get_contents($info_folder . 'storage_type.txt');
            if ($saved_storage_type == $storage_type) {
                return;
            }
        }
        $this->storage_type_changed = true;
        file_put_contents($info_folder . 'storage_type.txt', $storage_type);
    }

    /**
     * Initialize file_types.txt
     *
     * Check file_types.txt exist. If yes get it's content and check with
     * current file types. If not create it. If it dos'ent exist or value mismatched,
     * set $file_types_changed class property as true and write to file_types.txt
     *
     * Note: This file is just for storage_type === 1
     *
     * @param string $info_folder       Info folder path
     * @param string $storage_type      Storage type
     * @param string $file_types_string File types as string
     */
    private function initInfoFolderFileTypes($info_folder, $storage_type, $file_types_string)
    {
        $saved_file_types = '';
        if (file_exists($info_folder . 'file_types.txt')) {
            $saved_file_types = file_get_contents($info_folder . 'file_types.txt');
        }

        if ($storage_type != '1') {
            if (file_exists($info_folder . 'file_types.txt')) {
                unlink($info_folder . 'file_types.txt');
            }

            return;
        }
        if ($saved_file_types != $file_types_string) {
            $this->file_types_changed = true;
            file_put_contents($info_folder . 'file_types.txt', $file_types_string);
        }
    }

    /**
     * Initialize .htaccess file
     *
     * Check storage type and do appropriate action.
     *      Actions;
     *          Storage type === 3
     *              If .htaccess or .htaccess_checksum exist, remove them
     *          Storage type !== 3
     *              Check .htaccess and .htaccess_checksum exist. Then .htaccess saved sha1 checksum
     *              with current file sha1 checksum. After all if any of them dos'ent exist or mismatched
     *              checksum or changes in any .info folder files, write new .htaccess
     *
     * @param string $upload_folder     Upload folder path
     * @param string $storage_type      Storage type
     * @param string $file_types_string File types as string
     */
    private function initHtaccess($upload_folder, $storage_type, $file_types_string)
    {
        $htaccess_file           = $upload_folder . '.htaccess';
        $htaccess_checksum_file  = $upload_folder . '.htaccess_checksum';
        $htaccess_exist          = file_exists($htaccess_file);
        $htaccess_checksum_exist = file_exists($htaccess_checksum_file);
        if ($storage_type == '3') {
            if ($htaccess_exist) {
                unlink($htaccess_file);
            }
            if ($htaccess_checksum_exist) {
                unlink($htaccess_checksum_file);
            }

            return;
        }

        $htaccess_sha1_checksum = '';
        if ($htaccess_exist) {
            $htaccess_sha1_checksum = sha1_file($htaccess_file);
        }

        $htaccess_saved_checksum = '';
        if ($htaccess_checksum_exist) {
            $htaccess_saved_checksum = file_get_contents($htaccess_checksum_file);
        }

        if (
            ( ! $htaccess_exist) || ( ! $htaccess_checksum_exist) || ($this->storage_type_changed) || ($this->file_types_changed)
            || (empty($htaccess_sha1_checksum))
            || $htaccess_sha1_checksum != $htaccess_saved_checksum
        ) {
            $this->writeHtaccess($htaccess_file, $htaccess_checksum_file, $storage_type, $file_types_string);
        }
    }

    /**
     * Write new .htaccess and it's sha1 checksum
     *
     * @param string $htaccess_file          .htaccess file path
     * @param string $htaccess_checksum_file .htaccess_checksum file path
     * @param string $storage_type           Storage type
     * @param string $file_types_string      File types as string
     */
    private function writeHtaccess($htaccess_file, $htaccess_checksum_file, $storage_type, $file_types_string)
    {
        $Htaccess = new Htaccess();
        if ($storage_type == '1') {
            $Htaccess->setFileTypes($file_types_string);
        }
        file_put_contents($htaccess_file, $Htaccess->getContent());
        $htaccess_sha1_checksum = sha1_file($htaccess_file);
        file_put_contents($htaccess_checksum_file, $htaccess_sha1_checksum);
    }

    /**
     * Create path using an array
     *
     * Example: $path_array('16','01','01') => (created_path) upload_folder\16\01\01
     *
     * @param string $upload_folder Base path
     * @param array  $path_array    Path as array
     */
    private function createPath($upload_folder, $path_array)
    {
        if ( ! empty($path_array)) {
            $path_string = '';
            foreach ($path_array as $folder_name) {
                $path_string .= DIRECTORY_SEPARATOR . $folder_name;
                if ( ! is_dir($upload_folder . $path_string)) {
                    mkdir($upload_folder . $path_string);
                }
            }
        }
    }

    /**
     * Move uploaded file to upload folder by a path
     *
     * Move new uploaded file that is now in PHP tmp_folder to upload folder
     * using an array as it's path relative to upload_folder root. If new path dos'ent exist
     * then it will be created.
     *
     * @param array  $path_array  New upload path as array
     * @param string $path_string New upload path as string
     * @param string $tmp_name    Uploaded file tmp_name in $_FILES
     * @param string $name        Uploaded file new name
     * @param string $extension   Uploaded file new extension
     *
     * @return bool
     */
    public function moveUploadedFile($path_array, $path_string, $tmp_name, $name, $extension)
    {
        $upload_folder = $this->SecureUPloadConfig->get('upload_folder');
        $this->createPath($upload_folder, $path_array);

        return move_uploaded_file($tmp_name, $upload_folder . $path_string . $name . '.' . $extension);
    }
}