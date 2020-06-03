<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Ratepay\RatepayPayments\Components\Checkout\Subscriber;

use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Shopware\Core\PlatformRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\MultiConstraint;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckoutValidationSubscriber implements EventSubscriberInterface
{

    /** @var Validator */
    private $validator;

    /** @var RequestStack */
    private $requestStack;

    /** @var ContainerInterface */
    private $container;

    public function __construct(
        DataValidator $validator,
        RequestStack $requestStack,
        ContainerInterface $container
    ) {
        $this->validator = $validator;
        $this->requestStack  = $requestStack;
        $this->container = $container;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'framework.validation.order.create' => 'validateOrderData',
        ];
    }

    public function validateOrderData(BuildValidationEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return;
        }

        $context = $this->getContextFromRequest($request);
        $formattedPaymentHandlerIdentifier = $context->getPaymentMethod()->getFormattedHandlerIdentifier();
        $paymentHandlerIdentifier = $context->getPaymentMethod()->getHandlerIdentifier();

        if (strpos( $paymentHandlerIdentifier, 'RatepayPayments') !== false){

            $paymentHandler = $this->container->get($formattedPaymentHandlerIdentifier);
            $paymentMethodValidationDefinitions = $paymentHandler->getValidationDefinitions();

            $orderData = $request->request->all();

            foreach ($paymentMethodValidationDefinitions as $key => $value){
                foreach ($value as $singleConstraint){
                    $event->getDefinition()->add($key, $singleConstraint);
                }
            }

            $violations = $this->validator->validate($orderData, $event->getDefinition());

            if ($violations === null) {
                return;
            }

            throw new ConstraintViolationException($violations, $ratepayData);

        }

    }

    private function getContextFromRequest($request): SalesChannelContext
    {
        return $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
    }

}
