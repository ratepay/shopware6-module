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
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\NotBlank;

class CheckoutValidationSubscriber implements EventSubscriberInterface
{

    /** @var Validator */
    private $validator;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(
        DataValidator $validator,
        RequestStack $requestStack
    ) {
        $this->validator = $validator;
        $this->requestStack  = $requestStack;
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
        $paymentHandlerIdentifier = $request->attributes->get('sw-sales-channel-context')->getPaymentMethod()->getHandlerIdentifier();

        if (null === $request) {
            return;
        }

        if (strpos( $paymentHandlerIdentifier, 'RatepayPayments') !== false){

            $ratepayData = $request->request->all();

            $definition = new DataValidationDefinition('ratepay.validate.checkout');

            // TODO @aarends getValidationDefinitions für die Felder anlegen und in PaymentHandler auslagern, vorerst NotBlank

            foreach ($ratepayData['ratepay'] as $key => $value) {
                $definition->add($key, new NotBlank());
            }

            $violations = $this->validator->validate($ratepayData['ratepay'], $definition);

            if ($violations === null) {
                return;
            }

            throw new ConstraintViolationException($violations, $ratepayData);

        }

    }

}
