<?php
/**
 * This file is part of the SecureUPload package.
 *
 * (c) Alireza Dabiri Nejad <me@allii.ir>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alirdn\SecureUPload\Config;

/**
 * Class SecureUPloadConfig
 *
 * @author Alireza Dabiri Nejad <me@allii.ir>
 */
class SecureUPloadConfig extends Config
{
    /**
     * SecureUPload default config
     *
     * @var array Default Configs
     */
    protected $default_config = array(
        'storage_type' => '1',
        'file_types'   => array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'),
        'organize_by'  => 'none',
        'min_filesize' => 0,
        'max_filesize' => 0
    );

    /**
     * SecureUPload main config
     *
     * Indexes:
     *      upload_folder string Path to the upload base folder.
     *
     *      storage_type  string Set how to store uploaded files.
     *          Values:
     *              1: Store somewhere in web root with direct access
     *              2: Store somewhere in web root without direct access
     *              3: Store somewhere out of web root
     *          Web root: Some path that PHP could be executed
     *          Direct access: Access the uploaded file with it's url
     *          inDirect access: Access the uploaded file using PHP
     *
     *      file_types   array An array of acceptable file extension next to it's mime type
     *
     *      organize_by  string Set how to organize uploaded files
     *          Values:
     *              none: No organization. Just move to upload_folder root
     *              type: Organize by type(ext). Move Uploaded files to sub folder of upload_folder root by it's ext
     *                  example: 01.jpg => upload_folder\jpeg\01.jpg
     *              date: Organize by date of upload. Move Uploaded files to sub folder of upload_folder root by it's date
     *                  example: 01.jpg (Uploaded in 2016 january 1sth)=> upload_folder\16\02\01\01.jpg
     *              typeThenDate: Organize by type(ext) then date of upload. Move Uploaded files to sub folder of upload_folder root by it's ext then date
     *                  example: 01.jpg (Uploaded in 2016 january 1sth)=> upload_folder\jpg\16\02\01\01.jpg
     *              dateThenType: Organize by date then type(ext) of upload. Move Uploaded files to sub folder of upload_folder root by it's date then ext
     *                  example: 01.jpg (Uploaded in 2016 january 1sth)=> upload_folder\16\02\01\jpg\01.jpg
     *      min_filesize: minimum accepted file size in bytes
     *      max_filesize: maximum accepted file size in bytes
     *
     * @var array Main Config
     */
    protected $config = array(
        'upload_folder' => '',
        'storage_type'  => '1',
        'file_types'    => array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'),
        'organize_by'   => 'none',
        'min_filesize'  => 0,
        'max_filesize'  => 0
    );

    /**
     * @var array Validation rules
     */
    protected $config_validation_rules = array(
        'storage_type' => 'in_array:1,2,3',
        'file_types'   => 'required|array',
        'organize_by'  => 'in_array:none,type,date,typeThenDate,dateThenType',
        'min_filesize' => 'numeric',
        'max_filesize' => 'numeric',
    );
}