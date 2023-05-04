<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentLock\Subscriber;

use Ratepay\RpayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentLock\Service\LockService;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class CheckoutValidationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly LockService $lockService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'framework.validation.order.create' => ['validatePaymentQuery', 15],
        ];
    }

    public function validatePaymentQuery(BuildValidationEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        $salesChannelContext = $this->getContextFromRequest($request);
        $paymentMethod = $salesChannelContext->getPaymentMethod();
        $paymentHandlerIdentifier = $paymentMethod->getHandlerIdentifier();

        if (str_contains($paymentHandlerIdentifier, 'RpayPayments') && $salesChannelContext->getCustomer()) {
            $isPaymentMethodLocked = $this->lockService->isPaymentLocked(
                $paymentMethod->getId(),
                $salesChannelContext->getCustomer()->getId(),
                $salesChannelContext->getContext()
            );

            if ($isPaymentMethodLocked) {
                throw new ConstraintViolationException(new ConstraintViolationList([new ConstraintViolation('', '', [], null, '/ratepay', $salesChannelContext->getPaymentMethod()->getName(), null, AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . 'METHOD_NOT_AVAILABLE')]), $request->request->all());
            }
        }
    }

    private function getContextFromRequest(Request $request): SalesChannelContext
    {
        return $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
    }
}
