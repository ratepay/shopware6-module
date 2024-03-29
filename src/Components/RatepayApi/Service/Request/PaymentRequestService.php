<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service\Request;

use Exception;
use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use RatePAY\RequestBuilder;
use Ratepay\RpayPayments\Components\FeatureFlags\Util\FeatureFlagService;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileByOrderEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileSearchService;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\CustomerFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ExternalFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\PaymentFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method RequestBuilder doRequest(PaymentRequestData $requestData)
 */
class PaymentRequestService extends AbstractRequest
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

    /**
     * @var string
     */
    final public const EVENT_INIT_REQUEST = self::class . parent::EVENT_INIT_REQUEST;

    protected string $_operation = self::CALL_PAYMENT_REQUEST;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HeadFactory $headFactory,
        private readonly ProfileSearchService $profileConfigSearch,
        private readonly ProfileByOrderEntity $profileConfigOrderSearch,
        private readonly ShoppingBasketFactory $shoppingBasketFactory,
        private readonly CustomerFactory $customerFactory,
        private readonly PaymentFactory $paymentFactory,
        private readonly ExternalFactory $externalFactory
    ) {
        parent::__construct($eventDispatcher, $headFactory);
    }

    protected function initRequest(AbstractRequestData $requestData): void
    {
        /** @var PaymentRequestData $requestData */
        if ($requestData->getRatepayTransactionId() === null) {
            throw new Exception('no transaction id given'); // TODO add exception
        }
    }

    protected function getRequestContent(AbstractRequestData $requestData): ?Content
    {
        /** @var PaymentRequestData $requestData */
        return (new Content())
            ->setShoppingBasket($this->shoppingBasketFactory->getData($requestData))
            ->setCustomer($this->customerFactory->getData($requestData))
            ->setPayment($this->paymentFactory->getData($requestData));
    }

    protected function getRequestHead(AbstractRequestData $requestData): Head
    {
        /** @var PaymentRequestData $requestData */
        $head = parent::getRequestHead($requestData);
        $head->setExternal($this->externalFactory->getData($requestData));
        if ($requestData->getRatepayTransactionId() !== null) {
            $head->setTransactionId($requestData->getRatepayTransactionId());
        }

        return $head;
    }

    protected function getProfileConfig(AbstractRequestData $requestData): ProfileConfigEntity
    {
        if ($requestData->getProfileConfig() instanceof ProfileConfigEntity) {
            // the given profile config should be prioritised
            return $requestData->getProfileConfig();
        }

        /** @var PaymentRequestData $requestData */
        $search = $this->profileConfigOrderSearch->createSearchObject($requestData->getOrder());
        $search->setPaymentMethodId($requestData->getTransaction()->getPaymentMethodId());

        return $this->profileConfigSearch->search($search)->first();
    }

    protected function supportsRequestData(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof PaymentRequestData;
    }

    protected function isRequestBlockedByFeatureFlag(AbstractRequestData $requestData): bool
    {
        return FeatureFlagService::isFlagEnabled('FF-BLOCK-PR');
    }
}
