<?php

namespace Ratepay\RatepayPayments\Core\RatepayApi\Services;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;

class FileLogger extends Logger
{

    const FILENAME = 'ratepay.log';

    public function __construct($logDir)
    {
        parent::__construct('ratepay', [], []);
        $this->pushHandler(new RotatingFileHandler($logDir . '/' . self::FILENAME));
    }
}
