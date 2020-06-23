<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Ratepay\RatepayPayments\Components\Checkout\Subscriber;

use Ratepay\RatepayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CheckoutValidationSubscriber implements EventSubscriberInterface
{

    /** @var RequestStack */
    private $requestStack;

    /** @var ContainerInterface */
    private $container;

    public function __construct(
        DataValidator $validator,
        RequestStack $requestStack,
        ContainerInterface $container
    )
    {
        $this->validator = $validator;
        $this->requestStack = $requestStack;
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
        $paymentHandlerIdentifier = $context->getPaymentMethod()->getHandlerIdentifier();

        if (strpos($paymentHandlerIdentifier, 'RatepayPayments') !== false) {

            /** @var $paymentHandler AbstractPaymentHandler */
            $paymentHandler = $this->container->get($paymentHandlerIdentifier);

            $validationDefinitions = $paymentHandler->getValidationDefinitions($context);

            $definitions = new DataValidationDefinition();
            $this->addSubConstraints($definitions, $validationDefinitions);
            $event->getDefinition()->addSub('ratepay', $definitions);
        }

    }

    private function getContextFromRequest($request): SalesChannelContext
    {
        return $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
    }

    protected function addSubConstraints(DataValidationDefinition $parent, array $children)
    {
        foreach ($children as $key => $constraints) {
            if ($constraints instanceof DataValidationDefinition) {
                $parent->addSub($key, $constraints);
            } else {
                call_user_func_array([$parent, 'add'], [$key] + $constraints);
            }
        }
    }
}