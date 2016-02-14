<?php

namespace Alirdn\SecureUPload;

class headersClass {
	public static $headers = array();

	public static function resetHeaders() {
		self::$headers = array();
	}
}

function header( $header ) {
	headersClass::$headers[] = $header;
}
