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

$Uploads = $SecureUPload->uploadFiles( 'file' );

?>
<div style="float: left; width: 50%; font-family: monospace;">
	== SecureUPload Config ==
	<?php $SecureUPloadConfig->printAll(); ?>
	== FILES ==
	<pre><?php print_r( $_FILES ); ?></pre>
</div>
<div style="float: left; width: 50%; font-family: monospace;">
	<form method="post" enctype="multipart/form-data">
		<input type="file" name="file[]" multiple/>
		<input type="submit" value="Upload"/>
	</form>
	== Uploads ==
	<pre><?php print_r( $Uploads ); ?></pre>
</div>