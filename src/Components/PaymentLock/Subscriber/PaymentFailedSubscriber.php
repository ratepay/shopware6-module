<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentLock\Subscriber;

use RatePAY\Model\Response\AbstractResponse;
use Ratepay\RpayPayments\Components\PaymentLock\Service\LockService;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\CheckoutOperationInterface;
use Ratepay\RpayPayments\Components\RatepayApi\Event\RequestDoneEvent;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentFailedSubscriber implements EventSubscriberInterface
{
    /**
     * @var int[]
     */
    final public const ERROR_CODES = [703, 720, 721];

    public function __construct(
        private readonly LockService $lockService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestDoneEvent::class => 'lockPaymentMethod',
        ];
    }

    public function lockPaymentMethod(RequestDoneEvent $event): void
    {
        $requestData = $event->getRequestData();
        $response = $event->getRequestBuilder()->getResponse();
        if (!$requestData instanceof CheckoutOperationInterface || !$response instanceof AbstractResponse) {
            return;
        }

        if (in_array((int) $response->getReasonCode(), self::ERROR_CODES, false)) {
            if (!$requestData->getSalesChannelContext()->getCustomer() instanceof CustomerEntity) {
                // customer is not logged in - guest order
                return;
            }

            $this->lockService->lockPaymentMethod(
                $requestData->getContext(),
                $requestData->getSalesChannelContext()->getCustomer()->getId(),
                [$requestData->getPaymentMethodId()]
            );
        }
    }
}
