<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
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
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileBySalesChannelContext;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileSearchService;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\CustomerFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\AbstractRequest;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method RequestBuilder doRequest(PaymentQueryData $requestData)
 */
class PaymentQueryService extends AbstractRequest
{
    /**
     * @var string
     */
    final public const EVENT_SUCCESSFUL = self::class . parent::EVENT_SUCCESSFUL;

    /**
     * @var string
     */
    final public const EVENT_FAILED = self::class . parent::EVENT_FAILED;

    /**
     * @var string
     */
    final public const EVENT_BUILD_HEAD = self::class . parent::EVENT_BUILD_HEAD;

    /**
     * @var string
     */
    final public const EVENT_BUILD_CONTENT = self::class . parent::EVENT_BUILD_CONTENT;

    protected string $_operation = self::CALL_PAYMENT_QUERY;

    protected ?string $_subType = 'full';

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HeadFactory $headFactory,
        private readonly ShoppingBasketFactory $shoppingBasketFactory,
        private readonly CustomerFactory $customerFactory,
        private readonly ProfileBySalesChannelContext $salesChannelSearch,
        private readonly ProfileSearchService $profileSearchService
    ) {
        parent::__construct($eventDispatcher, $headFactory);
    }

    protected function getRequestHead(AbstractRequestData $requestData): Head
    {
        /** @var PaymentQueryData $requestData */
        $head = parent::getRequestHead($requestData);
        $head->setTransactionId($requestData->getTransactionId());

        return $head;
    }

    protected function getRequestContent(AbstractRequestData $requestData): ?Content
    {
        /** @var PaymentQueryData $requestData */
        return (new Content())
            ->setShoppingBasket($this->shoppingBasketFactory->getData($requestData))
            ->setCustomer($this->customerFactory->getData($requestData));
    }

    protected function getProfileConfig(AbstractRequestData $requestData): ?ProfileConfigEntity
    {
        /** @var PaymentQueryData $requestData */
        /** @var RequestDataBag $ratepayData */
        $ratepayData = $requestData->getRequestDataBag()->get('ratepay');

        if ($ratepayData->has('profile_uuid')) {
            return $this->profileSearchService->getProfileConfigById($ratepayData->get('profile_uuid'));
        }

        return $this->salesChannelSearch->search(
            $this->salesChannelSearch->createSearchObject($requestData->getSalesChannelContext())
                ->setTotalAmount($requestData->getCart()->getPrice()->getTotalPrice())
        )->first();
    }

    protected function supportsRequestData(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof PaymentQueryData;
    }
}
