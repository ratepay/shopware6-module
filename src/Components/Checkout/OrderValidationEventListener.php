<?php

declare(strict_types=1);

namespace Ratepay\RatepayPayments\Components\Checkout;

use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderValidationEventListener implements EventSubscriberInterface
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

        if (null === $request) {
            return;
        }

        $ratepayData = $request->request->all();

        $definition = new DataValidationDefinition('ratepay.validate.checkout');

        foreach ($ratepayData['ratepay'] as $key => $customerData) {
            if (!is_array($customerData)){
                if ($customerData === ''){
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
    private function getContextFromRequest(Request $request): SalesChannelContext
    {
        return $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
    }


}
