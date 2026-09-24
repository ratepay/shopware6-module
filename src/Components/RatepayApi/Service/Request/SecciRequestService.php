<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service\Request;

use RatePAY\Model\Request\SubModel\Content;
use RatePAY\RequestBuilder;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\SecciRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\CartPaymentFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\CartShoppingBasketFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\SecciFactory;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method RequestBuilder doRequest(SecciRequestData $requestData)
 */
class SecciRequestService extends AbstractRequest
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

    protected string $_operation = self::CALL_SECCI_REQUEST;

    public function __construct(
        EventDispatcherInterface                   $eventDispatcher,
        HeadFactory                                $headFactory,
        private readonly CartShoppingBasketFactory $cartShoppingBasketFactory,
        private readonly SecciFactory              $secciFactory,
        private readonly CartPaymentFactory        $cartPaymentFactory,
    )
    {
        parent::__construct($eventDispatcher, $headFactory);
    }

    protected function getRequestContent(AbstractRequestData $requestData): ?Content
    {
        /** @var SecciRequestData $requestData */
        return (new Content())
            ->setShoppingBasket($this->cartShoppingBasketFactory->getData($requestData))
            ->setSecci($this->secciFactory->getData($requestData))
            ->setPayment($this->cartPaymentFactory->getData($requestData));
    }

    protected function supportsRequestData(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof SecciRequestData;
    }
}
