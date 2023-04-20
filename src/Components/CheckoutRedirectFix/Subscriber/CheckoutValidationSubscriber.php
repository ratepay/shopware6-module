<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\CheckoutRedirectFix\Subscriber;

use Ratepay\RpayPayments\Components\CheckoutRedirectFix\Helper\AddressHelper;
use Ratepay\RpayPayments\Util\RequestHelper;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\ConstraintViolationList;

class CheckoutValidationSubscriber implements EventSubscriberInterface
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'framework.validation.order.create' => ['validatePaymentQuery', 30],
        ];
    }

    public function validatePaymentQuery(BuildValidationEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        $context = $this->getContextFromRequest($request);
        $paymentHandlerIdentifier = $context->getPaymentMethod()->getHandlerIdentifier();

        if (strpos($paymentHandlerIdentifier, 'RpayPayments') !== false) {
            $ratepayData = RequestHelper::getArray($request, 'ratepay');
            $billingMd5 = $ratepayData['validation']['billing_address_md5'] ?? null;
            $shippingMd5 = $ratepayData['validation']['shipping_address_md5'] ?? null;

            if ($billingMd5 !== AddressHelper::createMd5Hash($context->getCustomer()->getActiveBillingAddress()) ||
                $shippingMd5 !== AddressHelper::createMd5Hash($context->getCustomer()->getActiveShippingAddress())
            ) {
                throw new ConstraintViolationException(new ConstraintViolationList([]), $request->request->all());
            }
        }
    }

    private function getContextFromRequest(Request $request): SalesChannelContext
    {
        return $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
    }
}
