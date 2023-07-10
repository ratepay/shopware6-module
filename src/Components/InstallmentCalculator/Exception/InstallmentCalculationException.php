<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Exception;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InstallmentCalculationException extends ShopwareHttpException
{
    public function __construct(string $message = null, array $parameters = [], ?Throwable $e = null)
    {
        $message = 'Calculation of the installment plan was not successful. ' . $message;

        parent::__construct(trim($message), $parameters, $e);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_PRECONDITION_FAILED;
    }

    public function getErrorCode(): string
    {
        return 'RP_INSTALLMENT_CALCULATION_ERROR';
    }
}
