<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service\Request;

use RatePAY\Model\Request\SubModel\Content;
use RatePAY\RequestBuilder;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\CustomerFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\PaymentFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Shopware\Core\Framework\Context;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method RequestBuilder doRequest(Context $context, PaymentRequestData $requestData)
 */
class PaymentRequestService extends AbstractRequest
{
    public const EVENT_SUCCESSFUL = self::class . parent::EVENT_SUCCESSFUL;

    public const EVENT_FAILED = self::class . parent::EVENT_FAILED;

    public const EVENT_BUILD_HEAD = self::class . parent::EVENT_BUILD_HEAD;

    public const EVENT_BUILD_CONTENT = self::class . parent::EVENT_BUILD_CONTENT;

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

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HeadFactory $headFactory,
        ShoppingBasketFactory $shoppingBasketFactory,
        CustomerFactory $customerFactory,
        PaymentFactory $paymentFactory
    ) {
        parent::__construct($eventDispatcher, $headFactory);
        $this->shoppingBasketFactory = $shoppingBasketFactory;
        $this->customerFactory = $customerFactory;
        $this->paymentFactory = $paymentFactory;
    }

    protected function getRequestContent(AbstractRequestData $requestData): ?Content
    {
        /* @var PaymentRequestData $requestData */
        return (new Content())
            ->setShoppingBasket($this->shoppingBasketFactory->getData($requestData))
            ->setCustomer($this->customerFactory->getData($requestData))
            ->setPayment($this->paymentFactory->getData($requestData));
    }
}
