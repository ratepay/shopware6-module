<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
