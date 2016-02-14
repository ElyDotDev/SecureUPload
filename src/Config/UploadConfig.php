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
 * Class UploadConfig
 *
 * @author Alireza Dabiri Nejad <me@allii.ir>
 */
class UploadConfig extends Config
{
    /**
     * @var array Default config
     */
    protected $default_config = array(
        'file_types'   => array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'),
        'min_filesize' => 0,
        'max_filesize' => 0
    );

    /**
     * @var array Main config
     *
     * Indexes:
     *      file_types   array An array of acceptable file extension next to it's mime type
     *
     *      min_filesize: minimum accepted file size in bytes
     *
     *      max_filesize: maximum accepted file size in bytes
     */
    protected $config = array(
        'file_types'   => array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'),
        'min_filesize' => 0,
        'max_filesize' => 0
    );

    /**
     * @var array Validation rules
     */
    protected $config_validation_rules = array(
        'file_types'   => 'required|array',
        'min_filesize' => 'numeric',
        'max_filesize' => 'numeric',
    );
}