<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentLock\Subscriber;

use Ratepay\RpayPayments\Components\PaymentLock\Service\LockService;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\CheckoutOperationInterface;
use Ratepay\RpayPayments\Components\RatepayApi\Event\RequestDoneEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentFailedSubscriber implements EventSubscriberInterface
{
    /**
     * @var int[]
     */
    public const ERROR_CODES = [703, 720, 721];

    private LockService $lockService;

    public function __construct(LockService $lockService)
    {
        $this->lockService = $lockService;
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
        if (!$requestData instanceof CheckoutOperationInterface || $response === null) {
            return;
        }

        if (in_array((int)$response->getReasonCode(), self::ERROR_CODES, false)) {
            if ($requestData->getSalesChannelContext()->getCustomer() === null) {
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
