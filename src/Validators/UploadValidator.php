<?php
/**
 * This file is part of the SecureUPload package.
 *
 * (c) Alireza Dabiri Nejad <me@allii.ir>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alirdn\SecureUPload\Validators;

use Alirdn\SecureUPload\Config\SecureUPloadConfig;
use Alirdn\SecureUPload\Config\UploadConfig;
use Alirdn\SecureUPload\Upload\Upload;

/**
 * Class UploadValidator
 *
 * @author Alireza Dabiri Nejad <me@allii.ir>
 */
class UploadValidator {
	/**
	 * Forbidden file extension
	 *
	 * @var array
	 */
	private $forbidden_file_extensions = array(
		'php',
		'php2',
		'php3',
		'php4',
		'php5',
		'php6',
		'php7',
		'php8',
		'phtml',
		'pl',
		'py',
		'js',
		'jsp',
		'asp',
		'htm',
		'html',
		'shtml',
		'sh',
		'cgi',
		'htaccess'
	);

	/**
	 * Validate an uploaded file
	 *
	 * For validation if a upload specific config provided, then will be used. If not, Global config in
	 * SecureUPloadConfig will be used.
	 *
	 * Valid upload code is 10. Other codes are invalid ones. More information: Upload Class
	 *
	 * @param Upload             $Upload             Upload object
	 * @param SecureUPloadConfig $SecureUPloadConfig SecureUpload Config
	 * @param UploadConfig|null  $UploadConfig       Specific upload config
	 *
	 * @return int
	 */
	public function validate( Upload $Upload, SecureUPloadConfig $SecureUPloadConfig, UploadConfig $UploadConfig = null ) {
		if ( is_null( $UploadConfig ) ) {
			$min_filesize        = $SecureUPloadConfig->get( 'min_filesize' );
			$max_filesize        = $SecureUPloadConfig->get( 'max_filesize' );
			$accepted_extensions = array_keys( $SecureUPloadConfig->get( 'file_types' ) );
			$accepted_file_types = $SecureUPloadConfig->get( 'file_types' );
		} else {
			$min_filesize        = $UploadConfig->get( 'min_filesize' );
			$max_filesize        = $UploadConfig->get( 'max_filesize' );
			$accepted_extensions = array_keys( $UploadConfig->get( 'file_types' ) );
			$accepted_file_types = $UploadConfig->get( 'file_types' );
		}

		$Upload_tmp_name = $Upload->getTmpInfo( 'tmp_name' );
		if ( $this->checkFileUploadedByPost( $Upload_tmp_name ) !== true ) {
			return 11;
		}

		$Upload_size = $Upload->getTmpInfo( 'size' );
		if ( $this->checkMinFileSize( $Upload_size, $min_filesize ) !== true ) {
			return 12;
		}

		if ( $this->checkMaxFileSize( $Upload_size, $max_filesize ) !== true ) {
			return 13;
		}

		$Upload_name      = $Upload->getTmpInfo( 'name' );
		$Upload_extension = $this->getExtByPath( $Upload_name );
		if ( $this->checkForbiddenExtensions( $Upload_extension ) !== true ) {
			return 14;
		}

		if ( $this->checkExtension( $Upload_extension, $accepted_extensions ) !== true ) {
			return 15;
		}


		if ( $this->checkMimeType( $Upload_tmp_name, $Upload_extension, $accepted_file_types ) !== true ) {
			return 16;
		}

		return 10;
	}

	/**
	 * Check that an upload really uploaded by post method
	 *
	 * @param string $Upload_tmp_name Upload file tmp name in $_FILES
	 *
	 * @return bool
	 */
	private function checkFileUploadedByPost( $Upload_tmp_name ) {
		return is_uploaded_file( $Upload_tmp_name );
	}

	/**
	 * Get extension using upload name
	 *
	 * @param string $Upload_name upload name
	 *
	 * @return string
	 */
	private function getExtByPath( $Upload_name ) {
		return pathinfo( $Upload_name, PATHINFO_EXTENSION );
	}

	/**
	 * Check minimum upload file size
	 *
	 * @param string $Upload_size  Upload temporary file size in $_FILES
	 * @param string $min_filesize Minimum acceptable file size
	 *
	 * @return bool
	 */
	private function checkMinFileSize( $Upload_size, $min_filesize ) {
		return $Upload_size >= $min_filesize;
	}

	/**
	 * Check maximum upload file size
	 *
	 * @param string $Upload_size  Upload temporary file size in $_FILES
	 * @param string $max_filesize Maximum acceptable file size
	 *
	 * @return bool
	 */
	private function checkMaxFileSize( $Upload_size, $max_filesize ) {
		return ( $max_filesize === 0 ) || $Upload_size < $max_filesize;
	}

	/**
	 * Check upload extension not in forbidden extensions
	 *
	 * @param string $Upload_extension Upload extension
	 *
	 * @return bool
	 */
	private function checkForbiddenExtensions( $Upload_extension ) {
		return ! in_array( $Upload_extension, $this->forbidden_file_extensions );
	}

	/**
	 * Check upload extension is in acceptable extensions
	 *
	 * @param string $Upload_extension Upload extension
	 * @param array  $accepted_extensions
	 *
	 * @return bool
	 */
	private function checkExtension( $Upload_extension, $accepted_extensions ) {
		return in_array( $Upload_extension, $accepted_extensions );
	}

	/**
	 * Check uploaded file in PHP tmp folder mime types with accepted file extension mime type
	 *
	 * @param string $Upload_tmp_name
	 * @param string $Upload_extension
	 * @param array  $accepted_file_types
	 *
	 * @return bool
	 */
	private function checkMimeType( $Upload_tmp_name, $Upload_extension, $accepted_file_types ) {
		$file_finfo      = new \finfo( FILEINFO_MIME_TYPE );
		$finfo_mime_type = $file_finfo->file( $Upload_tmp_name );

		return $finfo_mime_type == $accepted_file_types[ $Upload_extension ];
	}
}