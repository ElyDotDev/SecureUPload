<?php

namespace Alirdn\SecureUPloadTest;

use Alirdn\SecureUPload\Logger\Logger;

include_once dirname(dirname(__FILE__)) . '/__custom_functions/logger_class.php';

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testLoggerLogToErrorLogMethod()
    {
        $this->assertEquals('TESTLOGCONTENT', Logger::logToErrorLog('TESTLOGCONTENT'));
    }
}