<?php

namespace Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Service;

use RatePAY\Model\Response\PaymentQuery;
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto\PaymentQueryData;
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Service\Request\PaymentQueryService;
use Ratepay\RpayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Ratepay\RpayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RpayPayments\Exception\RatepayException;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class PaymentQueryValidatorService
{

    /**
     * @var string
     */
    public const CODE_METHOD_NOT_AVAILABLE = 'RP_METHOD_NOT_AVAILABLE';


    private PaymentQueryService $paymentQueryService;

    private ConfigService $configService;

    public function __construct(
        PaymentQueryService $paymentQueryService,
        ConfigService $configService
    )
    {
        $this->paymentQueryService = $paymentQueryService;
        $this->configService = $configService;
    }

    /**
     * @thores ConstraintViolationException
     */
    public function validate(Cart $cart, SalesChannelContext $context, string $transactionId, DataBag $dataBag): void
    {
        $paymentHandlerIdentifier = $context->getPaymentMethod()->getHandlerIdentifier();

        try {
            $requestBuilder = $this->paymentQueryService->doRequest(new PaymentQueryData(
                $context,
                $cart,
                $dataBag,
                $transactionId,
                $this->configService->isSendDiscountsAsCartItem(),
                $this->configService->isSendShippingCostsAsCartItem()
            ));
        } catch (RatepayException $ratepayException) {
            throw $this->createException(
                $context,
                $dataBag,
//                    AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . self::CODE_METHOD_NOT_AVAILABLE
                $ratepayException->getMessage()
            );
        }

        $response = $requestBuilder->getResponse();
        if ($response instanceof PaymentQuery && $response->getStatusCode() === 'OK') {
            if (!in_array($paymentHandlerIdentifier::RATEPAY_METHOD, $response->getAdmittedPaymentMethods(), true)) {
                throw $this->createException(
                    $context,
                    $dataBag,
                    AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . self::CODE_METHOD_NOT_AVAILABLE
                );
            }
        } else {
            throw $this->createException(
                $context,
                $dataBag,
                (string)$response->getReasonMessage()
            );
        }
    }

    private function createException(SalesChannelContext $context, DataBag $requestDataBag, string $code): ConstraintViolationException
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

        return new ConstraintViolationException(new ConstraintViolationList([$violation]), $requestDataBag->all());
    }

}
