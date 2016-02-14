<?php
include_once '../src/autoloader.php';

try {
	$SecureUPloadConfig = new Alirdn\SecureUPload\Config\SecureUPloadConfig;
	$SecureUPloadConfig->set( 'upload_folder', dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR );
	$SecureUPload = new Alirdn\SecureUPload\SecureUPload( $SecureUPloadConfig );
} catch ( Alirdn\SecureUPload\Exceptions\UploadFolderException $exception ) {
	echo "Exception: " . $exception->getMessage() . ' Code: ' . $exception->getCode() . ' Note: For more information check php error_log.';
	die();
}

if ( ! isset( $_GET['id'] ) ) {
	$_GET['id'] = '';
}

$SecureUPload->getUploadAsFile( $_GET['id'] );
exit;
