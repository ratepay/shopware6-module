<?php

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

    // TODO @aarends hier unbedingt die Prüfung auf die Zahlungsmethode einbauen, ansonsten kannst du bspw. nicht mit PayPal etc. zahlen.
    public function validateOrderData(BuildValidationEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return;
        }

        $ratepayData = $request->request->all();

        $definition = new DataValidationDefinition('ratepay.validate.checkout');

        // TODO @aarends allgemein: Die Dafinition sollte fest definiert sein. Denn wenn ich das Formular ohne dem Feld XXX abschicke, dann ist das auch nicht in der Definition.
        // TODO Vielleicht lagert man das auch ein bisschen in den PaymentHandler aus àla `getValidationDefinitions`, da dieser ja bestimmte Daten erwartet/benötigt.
        foreach ($ratepayData['ratepay'] as $key => $customerData) { // TODO @aarends `customerData`? sollte das nicht eigentlich `value` sein?
            if (!is_array($customerData)){ // TODO @aarends warum sollte das ein Array sein. Wenn es ein Array ist, ist der Wert falsch. so sehe ich das.
                if ($customerData === ''){ // TODO @aarends mit der definition definierst du, wie die daten aussehen sollten. der Validator validiert dann die daten nach der Definition. Daher ist dieser vergleich nicht notwendig.
                    $definition->add($key, new NotBlank());
                }
            }
        }

        $violations = $this->validator->getViolations($ratepayData, $definition);

        if (!$violations->count()) {
            return;
        }

        throw new ConstraintViolationException($violations, $ratepayData);

    }

}
