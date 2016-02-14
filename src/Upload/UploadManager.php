<?php
/**
 * This file is part of the SecureUPload package.
 *
 * (c) Alireza Dabiri Nejad <me@allii.ir>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alirdn\SecureUPload\Upload;

use Alirdn\SecureUPload\Config\SecureUPloadConfig;
use Alirdn\SecureUPload\Config\UploadConfig;
use Alirdn\SecureUPload\UploadFolder\UploadFolder;
use Alirdn\SecureUPload\Validators\UploadValidator;

/**
 * Class UploadManager
 *
 * All the actions that are needed for managing uploads is here!
 * Actions likes upload, get and remove.
 *
 * @author Alireza Dabiri Nejad <me@allii.ir>
 */
class UploadManager
{
    /**
     * @var object Config\SecureUPloadConfig
     */
    private $SecureUPloadConfig;
    /**
     * @var object UploadFolder
     */
    private $UploadFolder;

    /**
     * @var object UploadValidator
     */
    private $UploadValidator;

    /**
     * UploadManager constructor.
     *
     * @param UploadFolder       $UploadFolder
     * @param SecureUPloadConfig $SecureUPloadConfig
     */
    public function __construct(UploadFolder $UploadFolder, SecureUPloadConfig $SecureUPloadConfig)
    {
        $this->SecureUPloadConfig = $SecureUPloadConfig;
        $this->UploadFolder       = $UploadFolder;
    }

    /**
     * Upload a file that is now in PHP tmp folder.
     *
     * An object of Upload\Upload will be returned. You must check the status
     * and error of this object to check if file uploaded successfully or not.
     *
     * @param string            $id           Input name that is used to get uploaded file tmp info in $_FILES
     * @param UploadConfig|null $UploadConfig Optional Upload specific config
     *
     * @return Upload
     */
    public function uploadFile($id, UploadConfig $UploadConfig = null)
    {
        $Upload = new Upload;

        $upload_tmp_info = $this->getUploadTmpInfoById($id);
        $Upload->setTmpInfoArray($upload_tmp_info);

        $this->doUpload($Upload, $UploadConfig);

        return $Upload;
    }

    /**
     * Upload multiple files that is now in PHP tmp folder.
     *
     * An array of Upload\Upload corresponding to temporary uploaded files will be returned.
     * If no file is uploaded an empty array will be returned.
     * You must check and iterate over returned array for uploads status and errors.
     *
     * @param string            $id           Input name that is used to get uploaded files tmp info in $_FILES
     * @param UploadConfig|null $UploadConfig Optional Upload specific config
     *
     * @return array
     */
    public function uploadFiles($id, UploadConfig $UploadConfig = null)
    {

        $uploads_tmp_info = $this->getUploadsTmpInfoById($id);

        $Uploads = array();
        if ( ! empty($uploads_tmp_info)) {
            foreach ($uploads_tmp_info as $upload_tmp_info) {
                $Uploads[] = new Upload($upload_tmp_info);
            }
        }

        if (is_array($Uploads)) {
            foreach ($Uploads as $Upload) {
                if ($Upload instanceof Upload) {
                    $this->doUpload($Upload, $UploadConfig);
                }
            }
        }

        return $Uploads;
    }

    /**
     * Get an uploaded file as an Upload\Upload object by id.
     *
     * An id was given before when file upload completed using getUpload or getUploads.
     *
     * @param string $id An Id that is given before when a successful upload done.
     *
     * @return Upload
     */
    public function getUpload($id)
    {

        $Upload = $this->setUploadById($id);

        if (file_exists($Upload->path)) {
            $Upload->status = 1;
            $Upload->size   = filesize($Upload->path);
        } else {
            $Upload->status = 2;
            $Upload->error  = 18;
        }

        return $Upload;

    }

    /**
     * Remove an uploaded file using it's id
     *
     * @param string $id An Id that is given before when a successful upload done.
     *
     * @return bool
     */
    public function removeUpload($id)
    {

        $Upload = $this->setUploadById($id);

        if (file_exists($Upload->path)) {
            return unlink($Upload->path);
        }

        return false;

    }

    /**
     * Set an upload using an upload id
     *
     * @param string $id Upload id
     *
     * @return Upload
     */
    private function setUploadById($id)
    {
        $Upload    = new Upload;
        $parsed_id = $this->parseUploadId($id);
        if ( ! empty($parsed_id)) {
            $parsed_id_count = count($parsed_id);
            if ($parsed_id_count > 2) {
                $path                  = implode(DIRECTORY_SEPARATOR, array_slice($parsed_id, 0, $parsed_id_count - 2));
                $Upload->name          = $parsed_id[$parsed_id_count - 2];
                $Upload->ext           = $parsed_id[$parsed_id_count - 1];
                $Upload->id            = $id;
                $Upload->relative_path = $path . DIRECTORY_SEPARATOR . $Upload->name . '.' . $Upload->ext;
                $Upload->relative_url  = str_replace(DIRECTORY_SEPARATOR, '/', $Upload->relative_path);
                $Upload->path          = $this->SecureUPloadConfig->get('upload_folder') . $Upload->relative_path;
                $Upload->type          = $this->getUploadType($Upload);
            } elseif ($parsed_id_count == 2) {
                $Upload->name          = $parsed_id[0];
                $Upload->ext           = $parsed_id[1];
                $Upload->id            = $id;
                $Upload->relative_path = $Upload->name . '.' . $Upload->ext;
                $Upload->relative_url  = str_replace(DIRECTORY_SEPARATOR, '/', $Upload->relative_path);
                $Upload->path          = $this->SecureUPloadConfig->get('upload_folder') . $Upload->relative_path;
                $Upload->type          = $this->getUploadType($Upload);
            }
        }

        return $Upload;

    }

    /**
     * Parse upload
     *
     * @param string $id Upload id
     *
     * @return array
     */
    private function parseUploadId($id)
    {
        return explode('_', $id);
    }

    /**
     * Get upload mime type
     *
     * @param Upload $Upload
     *
     * @return string
     */
    private function getUploadType(Upload $Upload)
    {
        if (file_exists($Upload->path)) {
            $file_finfo      = new \finfo(FILEINFO_MIME_TYPE);
            $finfo_mime_type = $file_finfo->file($Upload->path);

            return $finfo_mime_type;
        }

        return '';
    }

    /**
     * Get single upload temporary info in $_FILES
     *
     * @param string $id Index id in $_FILES
     *
     * @return array
     */
    private function getUploadTmpInfoById($id)
    {
        if (isset($_FILES[$id])) {
            if ( ! is_array($_FILES[$id]['name'])) {
                return $_FILES[$id];
            }
        }

        return array();
    }

    /**
     * Get multiple upload temporary info in $_FILES normalized.
     *
     * @param string $id Index id in $_FILES
     *
     * @return array
     */
    private function getUploadsTmpInfoById($id)
    {
        if (isset($_FILES[$id])) {
            if (is_array($_FILES[$id]['name'])) {
                return $this->normalizeUploadsArray($id);
            }
        }

        return array();
    }

    /**
     * Normalize a multiple uploads index in $_FILES
     *
     * @param string $id Index id in $_FILES
     *
     * @return array
     */
    private function normalizeUploadsArray($id)
    {
        $normalized_array = array();
        foreach ($_FILES[$id]['name'] as $index => $name) {
            $normalized_array[$index] = array(
                'name'     => $name,
                'type'     => $_FILES[$id]['type'][$index],
                'tmp_name' => $_FILES[$id]['tmp_name'][$index],
                'error'    => $_FILES[$id]['error'][$index],
                'size'     => $_FILES[$id]['size'][$index],
            );
        }

        return $normalized_array;
    }

    /**
     * Check and save uploaded file that is now in PHP tmp folder.
     *
     * @param Upload            $Upload
     * @param UploadConfig|null $UploadConfig
     */
    private function doUpload(Upload $Upload, UploadConfig $UploadConfig = null)
    {
        $this->checkUploadTmpInfo($Upload);

        if ($Upload->status === 1) {
            $this->validateUpload($Upload, $UploadConfig);
            if ($Upload->status === 1) {
                $this->saveUpload($Upload);
            }
        }
    }

    /**
     * Check temporary info of uploaded file in $_FILES
     *
     * Checks if any file provided for upload. If yes, Check if any error isset by PHP.
     *
     * @param Upload $Upload Upload object
     */
    private function checkUploadTmpInfo(Upload $Upload)
    {
        $Upload_tmp_info_error = $Upload->getTmpInfo('error');
        if ($Upload_tmp_info_error === UPLOAD_ERR_NO_FILE) {
            $Upload->status = 0;
        } elseif ($Upload_tmp_info_error === UPLOAD_ERR_OK) {
            $Upload->status = 1;
        } else {
            $Upload->status = 2;
            $Upload->error  = $Upload_tmp_info_error;
        }
    }

    /**
     * Validate uploaded file
     *
     * Validate all aspects of uploaded file. More info in UploadValidator Class.
     *
     * @param Upload            $Upload       Upload Object
     * @param UploadConfig|null $UploadConfig Specific Upload config
     */
    private function validateUpload(Upload $Upload, UploadConfig $UploadConfig = null)
    {
        if ( ! $this->UploadValidator) {
            $this->UploadValidator = new UploadValidator;
        }

        if ( ! is_null($UploadConfig)) {
            $UploadConfig->parse();
        }

        $validation_status_code = $this->UploadValidator->validate($Upload, $this->SecureUPloadConfig, $UploadConfig);

        if ($validation_status_code === 10) {
            return;
        }

        $Upload->status = 2;
        $Upload->error  = $validation_status_code;

    }

    /**
     * Save uploaded file into upload folder.
     *
     * @param Upload $Upload Upload object
     */
    private function saveUpload(Upload $Upload)
    {
        $Upload_tmp_info_name     = $Upload->getTmpInfo('name');
        $Upload_tmp_info_tmp_name = $Upload->getTmpInfo('tmp_name');
        $Upload_name              = md5($Upload_tmp_info_name . time());
        $Upload_extension         = pathinfo($Upload_tmp_info_name, PATHINFO_EXTENSION);
        $Upload_path_array        = $this->getUploadPathAsArray($Upload_extension);
        if ( ! empty($Upload_path_array)) {
            $Upload_path_string = implode($Upload_path_array, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        } else {
            $Upload_path_string = '';
        }

        if ($this->UploadFolder->moveUploadedFile($Upload_path_array, $Upload_path_string, $Upload_tmp_info_tmp_name, $Upload_name, $Upload_extension)) {
            $Upload->name          = $Upload_name;
            $Upload->ext           = $Upload_extension;
            $Upload->relative_path = $Upload_path_string . $Upload_name . '.' . $Upload_extension;
            $Upload->relative_url  = str_replace(DIRECTORY_SEPARATOR, '/', $Upload->relative_path);
            $Upload->path          = $this->SecureUPloadConfig->get('upload_folder') . $Upload->relative_path;
            $Upload->id            = $this->getId($Upload);
            $Upload->size          = $Upload->getTmpInfo('size');
            $Upload->type          = $this->getUploadType($Upload);

        } else {
            $Upload->status = 2;
            $Upload->error  = 17;
        }
    }

    /**
     * Get new uploaded file saving to path according to organization type
     *
     * @param string $Upload_extension Upload extension
     *
     * @return array
     */
    private function getUploadPathAsArray($Upload_extension)
    {
        $organize_by = $this->SecureUPloadConfig->get('organize_by');
        $path        = array();
        if ($organize_by === 'type') {
            $path[0] = $Upload_extension;
        } elseif ($organize_by === 'date') {
            $path = array(
                date('y'),
                date('m'),
                date('d')
            );
        } elseif ($organize_by === 'typeThenDate') {
            $path = array(
                $Upload_extension,
                date('y'),
                date('m'),
                date('d')
            );
        } elseif ($organize_by === 'dateThenType') {
            $path = array(
                date('y'),
                date('m'),
                date('d'),
                $Upload_extension
            );
        }

        return $path;
    }

    /**
     * Get saved uploaded file id
     *
     * @param Upload $Upload Upload object
     *
     * @return string
     */
    private function getId(Upload $Upload)
    {
        return str_replace('.', '_', str_replace(DIRECTORY_SEPARATOR, '_', $Upload->relative_path));
    }
}