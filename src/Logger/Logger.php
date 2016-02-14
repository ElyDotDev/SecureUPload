<?php
/**
 * This file is part of the SecureUPload package.
 *
 * (c) Alireza Dabiri Nejad <me@allii.ir>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alirdn\SecureUPload\Logger;

/**
 * Class Logger
 *
 * Simple logger that log to PHP error_log file
 *
 * @author Alireza Dabiri Nejad <me@allii.ir>
 */
class Logger {
	/**
	 * Log a message to PHP error_log file
	 *
	 * @param string $log_text Log's text that will outputted to PHP error_log file
	 *
	 * @return bool
	 */
	public static function logToErrorLog( $log_text ) {
		return error_log( $log_text );
	}
}