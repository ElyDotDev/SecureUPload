<?php
/**
 * This file is part of the SecureUPload package.
 *
 * (c) Alireza Dabiri Nejad <me@allii.ir>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alirdn\SecureUPload;

use Alirdn\SecureUPload\Config\SecureUPloadConfig;
use Alirdn\SecureUPload\Exceptions\UploadFolderException;
use Alirdn\SecureUPload\Logger\Logger;
use Alirdn\SecureUPload\Upload\UploadManager;
use Alirdn\SecureUPload\UploadFolder\UploadFolder;
use Alirdn\SecureUPload\Config\UploadConfig;

/**
 * SecureUPload main class
 *
 * For upload files this class and it's methods must be used only.
 *
 * @author Alireza Dabiri Nejad <me@allii.ir>
 */
class SecureUPload {
	/**
	 * SecureUPload version
	 */
	const VERSION = '0.1.2';

	/**
	 * @var object Config\SecureUPloadConfig Stores provided config
	 */
	private $SecureUPloadConfig;

	/**
	 * @var object UploadFolder
	 */
	private $UploadFolder;

	/**
	 * @var object
	 * Upload\UploadManager
	 */
	private $UploadManager;

	/**
	 * SecureUPload constructor.
	 *
	 * When a new object created, Initialize SecureUPload two main part.
	 * Config\SecureUPloadConfig and UploadFolder
	 *
	 * @param SecureUPloadConfig $SecureUPloadConfig       An object of Config\SecureUPloadConfig
	 * @param bool|true          $initialize_upload_folder Optional should initialize UploadFolder?
	 */
	public function __construct( SecureUPloadConfig $SecureUPloadConfig, $initialize_upload_folder = true ) {
		$this->initSecureUploadConfig( $SecureUPloadConfig );
		$this->initUploadFolder( $initialize_upload_folder );
	}

	/**
	 * Initialize the SecureUPload given config part
	 *
	 * Check that `upload_folder` is set in given config. If not throw a UploadPathException
	 * Exception with 1 code.
	 *
	 * @param SecureUPloadConfig $SecureUPloadConfig An object of Config\SecureUPloadConfig
	 *
	 * @throws UploadFolderException
	 */
	private function initSecureUploadConfig( SecureUPloadConfig $SecureUPloadConfig ) {
		$this->SecureUPloadConfig = $SecureUPloadConfig;

		$upload_folder = $this->SecureUPloadConfig->get( 'upload_folder' );

		if ( ! empty( $upload_folder ) ) {
			$this->SecureUPloadConfig->parse();
		} else {
			Logger::logToErrorLog( 'SecureUPload\'s upload folder config is empty.' );
			throw new UploadFolderException( __NAMESPACE__ . ' Upload folder path is empty.', 1 );
		}
	}

	/**
	 *  Initialize the SecureUPload UploadFolder
	 *
	 * Check that given `upload_folder` exist or not. If not throw a UploadPathException
	 * Exception with 2 code.
	 *
	 * @param $initialize_upload_folder
	 *
	 * @throws UploadFolderException
	 */
	private function initUploadFolder( $initialize_upload_folder ) {
		$this->UploadFolder = new UploadFolder( $this->SecureUPloadConfig );

		if ( $this->UploadFolder->exist() === true ) {
			if ( $initialize_upload_folder ) {
				$this->UploadFolder->init();
			}
		} else {
			Logger::logToErrorLog( 'Upload folder dose\'nt exits. Path: ' . $this->SecureUPloadConfig->get( 'upload_folder' ) );
			throw new UploadFolderException( __NAMESPACE__ . ' Upload folder dose\'nt exits.', 2 );
		}
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
	 * @return Upload\Upload
	 */
	public function uploadFile( $id, UploadConfig $UploadConfig = null ) {
		if ( ! $this->UploadManager ) {
			$this->UploadManager = new UploadManager( $this->UploadFolder, $this->SecureUPloadConfig );
		}

		return $this->UploadManager->uploadFile( $id, $UploadConfig );
	}

	/**
	 * Upload multiple files that are now in PHP tmp folder.
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
	public function uploadFiles( $id, UploadConfig $UploadConfig = null ) {
		if ( ! $this->UploadManager ) {
			$this->UploadManager = new UploadManager( $this->UploadFolder, $this->SecureUPloadConfig );
		}

		return $this->UploadManager->uploadFiles( $id, $UploadConfig );
	}

	/**
	 * Get an uploaded file as an Upload\Upload object by id.
	 *
	 * An id was given before when file upload completed using getUpload or getUploads.
	 *
	 * @param string $id An Id that is given before when a successful upload done.
	 *
	 * @return Upload\Upload
	 */
	public function getUpload( $id ) {
		if ( ! $this->UploadManager ) {
			$this->UploadManager = new UploadManager( $this->UploadFolder, $this->SecureUPloadConfig );
		}

		return $this->UploadManager->getUpload( $id );
	}

	/**
	 * Get an uploaded file as file by id
	 *
	 * An id was given before when file upload completed using getUpload or getUploads.
	 *
	 * @param string $id An Id that is given before when a successful upload done.
	 */
	public function getUploadAsFile( $id ) {
		if ( ! $this->UploadManager ) {
			$this->UploadManager = new UploadManager( $this->UploadFolder, $this->SecureUPloadConfig );
		}

		$Upload = $this->UploadManager->getUpload( $id );
		if ( $Upload->status == 1 ) {
			header( 'Content-Type: ' . $Upload->type );
			header( 'Accept-Ranges: bytes' );
			header( 'Content-Length: ' . $Upload->size );
			readfile( $Upload->path );
		} else {
			header( $_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found' );
		}
	}

	/**
	 * Remove an uploaded file using it's id
	 *
	 * @param string $id An Id that is given before when a successful upload done.
	 *
	 * @return bool
	 */
	public function removeUpload( $id ) {
		if ( ! $this->UploadManager ) {
			$this->UploadManager = new UploadManager( $this->UploadFolder, $this->SecureUPloadConfig );
		}

		return $this->UploadManager->removeUpload( $id );
	}
}