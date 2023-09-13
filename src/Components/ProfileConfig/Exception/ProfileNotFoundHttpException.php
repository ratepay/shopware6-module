<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Exception;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class ProfileNotFoundHttpException extends ShopwareHttpException
{
    public function __construct()
    {
        parent::__construct('Profile-ID was not found.');
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_PRECONDITION_FAILED;
    }

    public function getErrorCode(): string
    {
        return 'PROFILE_ID_NOT_FOUND';
    }
}
