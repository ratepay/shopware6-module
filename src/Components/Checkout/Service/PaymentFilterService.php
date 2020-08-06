<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Service;

use Ratepay\RatepayPayments\Components\Checkout\Event\RatepayPaymentFilterEvent;
use Ratepay\RatepayPayments\Components\ProfileConfig\Model\Collection\ProfileConfigMethodCollection;
use Ratepay\RatepayPayments\Components\ProfileConfig\Service\ProfileConfigService;
use Ratepay\RatepayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Payment\PaymentMethodCollection;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PaymentFilterService
{

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ProfileConfigService
     */
    private $profileConfigService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ProfileConfigService $profileConfigService
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->profileConfigService = $profileConfigService;
    }


    public function filterPaymentMethods(PaymentMethodCollection $paymentMethodCollection, SalesChannelContext $salesChannelContext): PaymentMethodCollection
    {
        return $paymentMethodCollection->filter(function (PaymentMethodEntity $paymentMethod) use ($salesChannelContext) {
            if (MethodHelper::isRatepayMethod($paymentMethod->getHandlerIdentifier()) === false) {
                // payment method is not a ratepay method - so we won't check it.
                return true;
            }

            $profileConfig = $this->profileConfigService->getProfileConfigBySalesChannel(
                $salesChannelContext,
                $paymentMethod->getId()
            );

            if ($profileConfig === null) {
                // no profile config for this sales channel is found
                return false;
            }

            /** @var ProfileConfigMethodCollection $methodConfigs */
            $methodConfigs = $profileConfig->getPaymentMethodConfigs()->filterByMethod($paymentMethod->getId());
            $methodConfig = $methodConfigs->first();

            if ($methodConfig === null) {
                // no profile method config is found
                return null;
            }

            // trigger event to filter the payment methods
            /** @var RatepayPaymentFilterEvent $filterEvent */
            $filterEvent = $this->eventDispatcher->dispatch(new RatepayPaymentFilterEvent(
                $paymentMethod,
                $profileConfig,
                $methodConfig,
                $salesChannelContext
            ));

            return $filterEvent->isAvailable();
        });
    }

}
