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

/**
 * Class Upload
 *
 * @author Alireza Dabiri Nejad <me@allii.ir>
 */
class Upload
{

    /**
     * @var array Temporary info of uploaded file in $_FILES
     */
    private $tmp_info = array(
        'name'     => '',
        'type'     => '',
        'tmp_name' => '',
        'error'    => 4,
        'size'     => 0,
    );

    /**
     * Upload file status
     *
     * Status:
     *      0: No file uploaded. When tmp_info[error] == UPLOAD_ERR_NO_FILE
     *      1: File uploaded and moved to upload_folder successfully
     *      2: File uploaded in PHP tmp_folder but and error occurred. See error property
     *
     * @var int
     */
    public $status = 0;
    /**
     * Upload error code
     *
     * Error codes separated into two parts.
     * Codes between [0-9]. These codes are PHP's upload error codes.
     * Codes between [10-19]. These errors are new codes introduced by SecureUPload.
     *
     * Error codes:
     *      0: There is no error. Uploaded uploaded and moved to upload folder successfully.
     *      1: The uploaded file exceeds the upload_max_filesize directive in php.ini.
     *      2: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.
     *      3: The uploaded file was only partially uploaded.
     *      4: No file was uploaded.
     *      5: ---------------------
     *      6: Missing a temporary folder. Introduced in PHP 5.0.3.
     *      7: Failed to write file to disk. Introduced in PHP 5.1.0.
     *      8: A PHP extension stopped the file upload.
     *      9: **** Reserved for PHP ***
     *      10: *** Reserved for SecureUPload ***
     *      11: File was not uploaded via HTTP POST.
     *      12: File size is less than minimum acceptable file size.
     *      13: File size is more than maximum acceptable file size.
     *      14: File extension is in forbidden file extensions.
     *      15: File extension is not in acceptable extensions.
     *      16: File mime type is not in acceptable files mime types.
     *      17: Error occurred during moving uploaded file to uploads folder.
     *      18: Get missing upload with id.
     *      19: Remove missing upload with id.
     * Error code
     *
     * @var int
     */
    public $error = 0;

    /**
     * @var string Upload new name
     */
    public $name = "";

    /**
     * @var string Upload extension
     */
    public $ext = "";

    /**
     * @var string Upload id
     */
    public $id = "";

    /**
     * @var string Upload relative path
     */
    public $relative_path = "";

    /**
     * @var string Upload relative URL
     */
    public $relative_url = "";

    /**
     * @var string Upload full path
     */
    public $path = "";

    /**
     * @var int Upload file size
     */
    public $size = 0;

    /**
     * @var string Upload mime type
     */
    public $type = "";

    /**
     * Upload constructor.
     *
     * @param array $tmp_info_array Temporary uploaded info array saved in $_FILES
     */
    public function __construct($tmp_info_array = array())
    {
        $this->setTmpInfoArray($tmp_info_array);
    }

    /**
     * Set Upload tmp info by it's id and value
     *
     * @param string $tmp_info_id
     * @param string $tmp_info_value
     *
     * @return $this
     */
    public function setTmpInfo($tmp_info_id, $tmp_info_value)
    {
        if (isset($this->tmp_info[$tmp_info_id])) {
            $this->tmp_info[$tmp_info_id] = $tmp_info_value;
        }

        return $this;
    }

    /**
     * Set temporary uploaded info saved in $_FILES as array.
     *
     * @param array $tmp_info_array
     *
     * @return $this
     */
    public function setTmpInfoArray($tmp_info_array)
    {
        if (is_array($tmp_info_array) && ! empty($tmp_info_array)) {
            foreach ($tmp_info_array as $tmp_info_id => $tmp_info_value) {
                $this->setTmpInfo($tmp_info_id, $tmp_info_value);
            }
        }

        return $this;
    }

    /**
     * Get temporary info.
     *
     * @param string $tmp_info_id Id of index that want to get
     *
     * @return string
     */
    public function getTmpInfo($tmp_info_id)
    {
        if (isset($this->tmp_info[$tmp_info_id])) {
            return $this->tmp_info[$tmp_info_id];
        }

        return '';
    }

}