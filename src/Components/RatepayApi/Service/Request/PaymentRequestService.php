<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service\Request;

use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use RatePAY\RequestBuilder;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\ProfileConfigService;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\CustomerFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\PaymentFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Service\TransactionIdService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method RequestBuilder doRequest(PaymentRequestData $requestData)
 */
class PaymentRequestService extends AbstractRequest
{
    public const EVENT_SUCCESSFUL = self::class . parent::EVENT_SUCCESSFUL;

    public const EVENT_FAILED = self::class . parent::EVENT_FAILED;

    public const EVENT_BUILD_HEAD = self::class . parent::EVENT_BUILD_HEAD;

    public const EVENT_BUILD_CONTENT = self::class . parent::EVENT_BUILD_CONTENT;

    public const EVENT_INIT_REQUEST = self::class . parent::EVENT_INIT_REQUEST;

    protected $_operation = self::CALL_PAYMENT_REQUEST;

    /**
     * @var ShoppingBasketFactory
     */
    private $shoppingBasketFactory;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var ProfileConfigService
     */
    private $profileConfigService;

    /**
     * @var TransactionIdService
     */
    private $transactionIdService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HeadFactory $headFactory,
        TransactionIdService $transactionIdService,
        ProfileConfigService $profileConfigService,
        ShoppingBasketFactory $shoppingBasketFactory,
        CustomerFactory $customerFactory,
        PaymentFactory $paymentFactory
    ) {
        parent::__construct($eventDispatcher, $headFactory);
        $this->shoppingBasketFactory = $shoppingBasketFactory;
        $this->customerFactory = $customerFactory;
        $this->paymentFactory = $paymentFactory;
        $this->profileConfigService = $profileConfigService;
        $this->transactionIdService = $transactionIdService;
    }

    protected function initRequest(AbstractRequestData $requestData): void
    {
        /* @var PaymentRequestData $requestData */
        $transactionId = $this->transactionIdService->getTransactionId($requestData->getSalesChannelContext());
        $requestData->setRatepayTransactionId($transactionId);
    }

    protected function getRequestContent(AbstractRequestData $requestData): ?Content
    {
        /* @var PaymentRequestData $requestData */
        return (new Content())
            ->setShoppingBasket($this->shoppingBasketFactory->getData($requestData))
            ->setCustomer($this->customerFactory->getData($requestData))
            ->setPayment($this->paymentFactory->getData($requestData));
    }

    protected function getRequestHead(AbstractRequestData $requestData): Head
    {
        /* @var PaymentRequestData $requestData */
        $head = parent::getRequestHead($requestData);
        if ($requestData->getRatepayTransactionId()) {
            $head->setTransactionId($requestData->getRatepayTransactionId());
        }

        $head->setExternal($head->getExternal() ?? new Head\External());
        $head->getExternal()->setOrderId($requestData->getOrder()->getOrderNumber());

        return $head;
    }

    protected function getProfileConfig(AbstractRequestData $requestData): ProfileConfigEntity
    {
        /* @var $requestData PaymentRequestData */
        return $this->profileConfigService->getProfileConfigByOrderEntity(
            $requestData->getOrder(),
            $requestData->getTransaction()->getPaymentMethodId(),
            $requestData->getContext()
        );
    }

    protected function supportsRequestData(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof PaymentRequestData;
    }
}
