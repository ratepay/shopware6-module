<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Mock\RatepayApi\Service\Request;

use Ratepay\RpayPayments\Components\RatepayApi\Factory\ExternalFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\InvoiceFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentDeliverService;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Factory\Mock;
use Ratepay\RpayPayments\Tests\Mock\Repository\EntityRepositoryMock;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PaymentDeliverServiceMock extends PaymentDeliverService
{
    use tMock;

    public function __construct(
        HeadFactory $headFactory = null,
        ShoppingBasketFactory $shoppingBasketFactory = null,
        InvoiceFactory $invoiceFactory = null,
        ExternalFactory $externalFactory = null
    ) {
        parent::__construct(
            new EventDispatcher(),
            $headFactory ?? Mock::createHeadFactory(),
            new EntityRepositoryMock(),
            $shoppingBasketFactory ?? Mock::createShoppingBasketFactory(),
            $invoiceFactory ?? Mock::createInvoiceFactory(),
            $externalFactory ?? Mock::createExternalFactory(),
            );
    }
}
