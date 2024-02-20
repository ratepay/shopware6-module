<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Struct;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class PaymentDataResponse extends StoreApiResponse
{
    public function __construct(ArrayStruct $paymentExtension, int $status = self::HTTP_OK)
    {
        parent::__construct($paymentExtension);
        $this->setStatusCode($status);
    }
}
