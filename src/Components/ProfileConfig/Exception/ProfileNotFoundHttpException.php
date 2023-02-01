<?php

namespace Ratepay\RpayPayments\Components\ProfileConfig\Exception;

use Shopware\Core\Framework\HttpException;
use Symfony\Component\HttpFoundation\Response;

class ProfileNotFoundHttpException extends HttpException
{
    public function __construct()
    {
        parent::__construct(Response::HTTP_PRECONDITION_FAILED, '', 'Profile-ID was not found.');
    }
}
