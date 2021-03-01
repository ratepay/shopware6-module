<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Subscriber;

use RatePAY\Model\Response\PaymentQuery;
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto\PaymentQueryData;
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Service\Request\PaymentQueryService;
use Ratepay\RpayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Ratepay\RpayPayments\Exception\RatepayException;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidator;
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
    public const CODE_METHOD_NOT_AVAILABLE = 'RP_METHOD_NOT_AVAILABLE';

    /** @var RequestStack */
    private $requestStack;

    /**
     * @var PaymentQueryService
     */
    private $paymentQueryService;

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var DataValidator
     */
    private $dataValidator;

    public function __construct(
        RequestStack $requestStack,
        DataValidator $dataValidator,
        CartService $cartService,
        PaymentQueryService $paymentQueryService
    ) {
        $this->requestStack = $requestStack;
        $this->paymentQueryService = $paymentQueryService;
        $this->cartService = $cartService;
        $this->dataValidator = $dataValidator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'framework.validation.order.create' => ['validatePaymentQuery', 5],
        ];
    }

    public function validatePaymentQuery(BuildValidationEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return;
        }

        $context = $this->getContextFromRequest($request);
        /** @var AbstractPaymentHandler $paymentHandlerIdentifier */
        $paymentHandlerIdentifier = $context->getPaymentMethod()->getHandlerIdentifier();

        if (strpos($paymentHandlerIdentifier, 'RpayPayments') !== false) {
            // we must validate the data BEFORE we send the request to the gateway.
            // this is not the good way, but we do not have another possibility.
            // we just want to validate the ratepay-data to prevent unexpected behavior of third-party-plugins
            $definition = new DataValidationDefinition();
            $definition->addSub('ratepay', $event->getDefinition()->getSubDefinitions()['ratepay']);
            $this->dataValidator->validate(['ratepay' => $request->request->get('ratepay')], $definition);

            try {
                $requestBuilder = $this->paymentQueryService->doRequest(new PaymentQueryData(
                    $context,
                    $this->cartService->getCart($context->getToken(), $context),
                    new RequestDataBag($request->request->all()),
                    $request->request->get('ratepay')['transactionId']
                ));
            } catch (RatepayException $e) {
                $this->throwException(
                    $context,
                    $request,
//                    AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . self::CODE_METHOD_NOT_AVAILABLE
                    $e->getMessage()
                );
            }

            $response = isset($requestBuilder) ? $requestBuilder->getResponse() : null;
            if ($response instanceof PaymentQuery && $response->getStatusCode() === 'OK') {
                if (!in_array($paymentHandlerIdentifier::RATEPAY_METHOD, $response->getAdmittedPaymentMethods(), true)) {
                    $this->throwException(
                        $context,
                        $request,
                        AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . self::CODE_METHOD_NOT_AVAILABLE
                    );
                }
            } else {
                $this->throwException(
                    $context,
                    $request,
                    $response->getReasonMessage()
                );
            }
        }
    }

    private function getContextFromRequest($request): SalesChannelContext
    {
        return $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
    }

    private function throwException(SalesChannelContext $context, Request $request, string $code): void
    {
        $violation = new ConstraintViolation(
            '',
            '',
            [],
            null,
            '/ratepay',
            $context->getPaymentMethod()->getName(),
            null,
            $code
        );

        throw new ConstraintViolationException(new ConstraintViolationList([$violation]), $request->request->all());
    }
}
