<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Service\Request;

use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use RatePAY\RequestBuilder;
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto\PaymentQueryData;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\ProfileConfigService;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\CustomerFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\AbstractRequest;
use Ratepay\RpayPayments\Components\RatepayApi\Service\TransactionIdService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method RequestBuilder doRequest(PaymentQueryData $requestData)
 */
class PaymentQueryService extends AbstractRequest
{
    public const EVENT_SUCCESSFUL = self::class . parent::EVENT_SUCCESSFUL;

    public const EVENT_FAILED = self::class . parent::EVENT_FAILED;

    public const EVENT_BUILD_HEAD = self::class . parent::EVENT_BUILD_HEAD;

    public const EVENT_BUILD_CONTENT = self::class . parent::EVENT_BUILD_CONTENT;

    protected $_operation = self::CALL_PAYMENT_QUERY;

    protected $_subType = 'full';

    /**
     * @var ProfileConfigService
     */
    private $profileConfigService;

    /**
     * @var TransactionIdService
     */
    private $transactionIdService;

    /**
     * @var ShoppingBasketFactory
     */
    private $shoppingBasketFactory;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HeadFactory $headFactory,
        ShoppingBasketFactory $shoppingBasketFactory,
        CustomerFactory $customerFactory,
        ProfileConfigService $profileConfigService
    ) {
        parent::__construct($eventDispatcher, $headFactory);
        $this->shoppingBasketFactory = $shoppingBasketFactory;
        $this->customerFactory = $customerFactory;
        $this->profileConfigService = $profileConfigService;
    }

    protected function getRequestHead(AbstractRequestData $requestData): Head
    {
        /* @var PaymentQueryData $requestData */
        $head = parent::getRequestHead($requestData);
        $head->setTransactionId($requestData->getTransactionId());

        return $head;
    }

    protected function getRequestContent(AbstractRequestData $requestData): ?Content
    {
        /* @var PaymentQueryData $requestData */
        return (new Content())
            ->setShoppingBasket($this->shoppingBasketFactory->getData($requestData))
            ->setCustomer($this->customerFactory->getData($requestData));
    }

    protected function getProfileConfig(AbstractRequestData $requestData): ProfileConfigEntity
    {
        /* @var $requestData PaymentQueryData */
        return $this->profileConfigService->getProfileConfigBySalesChannel($requestData->getSalesChannelContext());
    }

    protected function supportsRequestData(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof PaymentQueryData;
    }
}
