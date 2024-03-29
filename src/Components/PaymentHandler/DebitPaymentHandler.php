<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler;

use Shopware\Core\Framework\Validation\DataBag\DataBag;

class DebitPaymentHandler extends AbstractPaymentHandler
{
    use DebitValidationTrait;

    /**
     * @var string
     */
    final public const RATEPAY_METHOD = 'ELV';

    public function getValidationDefinitions(DataBag $requestDataBag, $baseData): array
    {
        return array_merge(
            parent::getValidationDefinitions($requestDataBag, $baseData),
            $this->getDebitConstraints($requestDataBag, $baseData)
        );
    }

    public static function getRatepayPaymentMethodName(): string
    {
        return self::RATEPAY_METHOD;
    }
}
