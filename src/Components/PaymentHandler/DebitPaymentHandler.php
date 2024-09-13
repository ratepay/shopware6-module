<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class DebitPaymentHandler extends AbstractPaymentHandler
{
    use DebitValidationTrait;

    /**
     * @var string
     */
    final public const RATEPAY_METHOD = 'ELV';

    public function getValidationDefinitions(DataBag $requestDataBag, SalesChannelContext $salesChannelContext, OrderEntity $orderEntity = null): array
    {
        return array_merge(
            parent::getValidationDefinitions($requestDataBag, $salesChannelContext, $orderEntity),
            $this->getDebitConstraints($orderEntity ?: $salesChannelContext)
        );
    }

    public static function getRatepayPaymentMethodName(): string
    {
        return self::RATEPAY_METHOD;
    }
}
