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

/**
 * Class Htaccess
 *
 * Upload folder .htaccess content provider
 *
 * @author Alireza Dabiri Nejad <me@allii.ir>
 */
class Htaccess {
	/**
	 * @var string Allowed file types as string delimited by `|`
	 */
	private $file_types_string = '';

	/**
	 * @var string .htaccess main body
	 */
	private $htaccess_main_content = '
		IndexIgnore *
		AddHandler cgi-script .php .php2 .php3 .php4 .php5 .php6 .php7 .php8 .pl .py .js .jsp .asp .htm .html .shtml .sh .cgi
		Options -ExecCGI -Indexes
		RewriteRule ^(php\\.ini|\\.htaccess) - [NC,F]
		<LimitExcept GET POST>
		Deny from all
		</LimitExcept>
		php_flag engine off
		Order deny,allow
		Deny from all
		';

	/**
	 * @var string .htaccess part for storage_type === 1 for allowed file types
	 */
	private $htaccess_content_for_file_types = '
		<FilesMatch \"^[^.]+\.(?i:FILE_TYPES)$\">
		    Allow from all
		</FilesMatch>
	';

	/**
	 * Set allowed file types as string
	 *
	 * @param string $file_types_string Allowed file types as string delimited by `|`
	 */
	public function setFileTypes( $file_types_string = '' ) {
		$this->file_types_string = $file_types_string;
	}

	/**
	 * Get .htaccess content
	 *
	 * @return string
	 */
	public function getContent() {

		if ( empty( $this->file_types_string ) ) {
			$htaccess_content = $this->htaccess_main_content;
		} else {
			$htaccess_content = $this->htaccess_main_content . str_replace( "FILE_TYPES", $this->file_types_string, $this->htaccess_content_for_file_types );
		}

		return trim( preg_replace( '/\t+/', '', $htaccess_content ) );
	}
}