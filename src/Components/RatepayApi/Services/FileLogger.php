<?php

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class FileLogger extends Logger
{

    const FILENAME = 'ratepay.log';

    public function __construct($logDir)
    {
        parent::__construct('ratepay', [], []);
        $this->pushHandler(new RotatingFileHandler($logDir . '/' . self::FILENAME));
    }
}
