<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Mock\RatepayApi\Service\Request;

use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileByOrderEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileSearchService;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\CustomerFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ExternalFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\PaymentFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Factory\Mock;
use Ratepay\RpayPayments\Tests\Mock\Repository\EntityRepositoryMock;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PaymentRequestServiceMock extends PaymentRequestService
{
    use tMock;

    public function __construct(
        HeadFactory $headFactory = null,
        ShoppingBasketFactory $shoppingBasketFactory = null,
        CustomerFactory $customerFactory = null,
        PaymentFactory $paymentFactory = null,
        ExternalFactory $externalFactory = null
    )
    {
        parent::__construct(
            new EventDispatcher(),
            $headFactory ?? Mock::createHeadFactory(),
            $searchService = new ProfileSearchService(new EventDispatcher(), new EntityRepositoryMock(), new EntityRepositoryMock()),
            new ProfileByOrderEntity($searchService),
            $shoppingBasketFactory ?? Mock::createShoppingBasketFactory(),
            $customerFactory ?? Mock::createCustomerFactory(),
            $paymentFactory ?? Mock::createPaymentFactory(),
            $externalFactory ?? Mock::createExternalFactory()
        );
    }
}
