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
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentReturnService;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Factory\Mock;
use Ratepay\RpayPayments\Tests\Mock\Repository\EntityRepositoryMock;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PaymentReturnServiceMock extends PaymentReturnService
{
    use tMock;

    public function __construct(
        HeadFactory $headFactory = null,
        ShoppingBasketFactory $shoppingBasketFactory = null,
        ExternalFactory $externalFactory = null
    )
    {
        parent::__construct(
            new EventDispatcher(),
            $headFactory ?? Mock::createHeadFactory(),
            new EntityRepositoryMock(),
            $shoppingBasketFactory ?? Mock::createShoppingBasketFactory(),
            $externalFactory ?? Mock::createExternalFactory()
        );
    }
}
