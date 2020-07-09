<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Service;

use Ratepay\RatepayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RatepayPayments\Components\ProfileConfig\Service\ProfileConfigService;
use Ratepay\RatepayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Payment\PaymentMethodCollection;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PaymentFilterService
{

    /**
     * @var ProfileConfigService
     */
    protected $profileConfigService;

    public function __construct(ProfileConfigService $profileConfigService)
    {
        $this->profileConfigService = $profileConfigService;
    }

    public function filterPayments(PaymentMethodCollection $payments, SalesChannelContext $context): PaymentMethodCollection
    {
        $filteredMethods = [];
        foreach ($payments as $paymentMethod) {
            if (MethodHelper::isRatepayMethod($paymentMethod->getHandlerIdentifier())) {
                if ($this->isPaymentMethodAllowed($paymentMethod, $context)) {
                    $filteredMethods[] = $paymentMethod;
                }
            } else {
                $filteredMethods[] = $paymentMethod;
            }
        }
        return new PaymentMethodCollection($filteredMethods);
    }

    public function isPaymentMethodAllowed(PaymentMethodEntity $paymentMethodEntity, SalesChannelContext $context): bool
    {
        $paymentProfileConfig = $this->profileConfigService->getProfileConfigBySalesChannel(
            $context,
            $paymentMethodEntity->getId()
        );

        // With this logic the currency, billing address country and shipping address country
        // are already validated
        if ($paymentProfileConfig instanceof ProfileConfigEntity) {
            return false;
        }

        return !$this->isPaymentMethodLockedForCustomer()
               && ($this->areBillingAndShippingSame() || $this->areDifferentAddressesAllowed())
               && $this->isAmountAllowed();
    }

    protected function isPaymentMethodLockedForCustomer(): bool
    {
        // ToDo: RATESWSX-79
        return false;
    }

    protected function areBillingAndShippingSame(): bool
    {
        // ToDo: Finish function
        return true;
    }

    protected function areDifferentAddressesAllowed(): bool
    {
        // ToDo: Finish functions
        return true;
    }

    protected function isAmountAllowed(): bool
    {
        // ToDo: Finish function
        return true;
    }

}
