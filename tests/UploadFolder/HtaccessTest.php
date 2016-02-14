<?php

namespace Alirdn\SecureUPloadTest;

use Alirdn\SecureUPload\UploadFolder\Htaccess;

class HtaccessTest extends \PHPUnit_Framework_TestCase {
	public function testHtaccessWithoutFileTypes() {
		$htaccess = new Htaccess();

		$htaccess_desired_content = '
			IndexIgnore *
			AddHandler cgi-script .php .php2 .php3 .php4 .php5 .php6 .php7 .php8 .pl .py .js .jsp .asp .htm .html .shtml .sh .cgi
			Options -ExecCGI -Indexes
			RewriteRule ^(php\.ini|\.htaccess) - [NC,F]
			<LimitExcept GET POST>
			Deny from all
			</LimitExcept>
			php_flag engine off
			Order deny,allow
			Deny from all
			';

		$htaccess_content = $htaccess->getContent();

		$this->assertEquals( trim( preg_replace( '/\t+/', '', $htaccess_desired_content ) ), $htaccess_content );
	}

	public function testHtaccessWithFileTypesSimple() {
		$htaccess = new Htaccess();

		$htaccess_desired_content = '
			IndexIgnore *
			AddHandler cgi-script .php .php2 .php3 .php4 .php5 .php6 .php7 .php8 .pl .py .js .jsp .asp .htm .html .shtml .sh .cgi
			Options -ExecCGI -Indexes
			RewriteRule ^(php\.ini|\.htaccess) - [NC,F]
			<LimitExcept GET POST>
			Deny from all
			</LimitExcept>
			php_flag engine off
			Order deny,allow
			Deny from all

			<FilesMatch \"^[^.]+\.(?i:jpg)$\">
			    Allow from all
			</FilesMatch>
			';

		$htaccess->setFileTypes( 'jpg' );
		$htaccess_content = $htaccess->getContent();

		$this->assertEquals( trim( preg_replace( '/\t+/', '', $htaccess_desired_content ) ), $htaccess_content );
	}

	public function testHtaccessWithFileTypesComplex() {
		$htaccess = new Htaccess();

		$htaccess_desired_content = '
			IndexIgnore *
			AddHandler cgi-script .php .php2 .php3 .php4 .php5 .php6 .php7 .php8 .pl .py .js .jsp .asp .htm .html .shtml .sh .cgi
			Options -ExecCGI -Indexes
			RewriteRule ^(php\.ini|\.htaccess) - [NC,F]
			<LimitExcept GET POST>
			Deny from all
			</LimitExcept>
			php_flag engine off
			Order deny,allow
			Deny from all

			<FilesMatch \"^[^.]+\.(?i:jpg|jpeg|png|gif)$\">
			    Allow from all
			</FilesMatch>
			';

		$htaccess->setFileTypes( 'jpg|jpeg|png|gif' );
		$htaccess_content = $htaccess->getContent();

		$this->assertEquals( trim( preg_replace( '/\t+/', '', $htaccess_desired_content ) ), $htaccess_content );
	}
}