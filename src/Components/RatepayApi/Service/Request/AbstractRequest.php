<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service\Request;

use InvalidArgumentException;
use RatePAY\Exception\ExceptionAbstract;
use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use RatePAY\RequestBuilder;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundException;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Event\BuildEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Event\InitEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Event\RequestBuilderFailedEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Event\RequestDoneEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Event\ResponseEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Exception\RatepayException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractRequest
{
    /**
     * @var string
     */
    public const CALL_PAYMENT_REQUEST = 'PaymentRequest';

    /**
     * @var string
     */
    public const CALL_DELIVER = 'ConfirmationDeliver';

    /**
     * @var string
     */
    public const CALL_CHANGE = 'PaymentChange';

    /**
     * @var string
     */
    public const CALL_PROFILE_REQUEST = 'ProfileRequest';

    /**
     * @var string
     */
    public const CALL_PAYMENT_QUERY = 'PaymentQuery';

    /**
     * @var string
     */
    public const CALL_PAYMENT_INIT = 'PaymentInit';

    /**
     * @var string
     */
    protected const EVENT_SUCCESSFUL = '.successful';

    /**
     * @var string
     */
    protected const EVENT_FAILED = '.failed';

    /**
     * @var string
     */
    protected const EVENT_BUILD_HEAD = '.build.head';

    /**
     * @var string
     */
    protected const EVENT_BUILD_CONTENT = '.build.content';

    /**
     * @var string
     */
    protected const EVENT_INIT_REQUEST = '.init.request';

    /**
     * @deprecated
     */
    protected string $_operation;

    /**
     * @deprecated
     */
    protected ?string $_subType = null;

    private HeadFactory $headFactory;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HeadFactory $headFactory
    ) {
        $this->headFactory = $headFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws RatepayException
     */
    final public function doRequest(AbstractRequestData $requestData): RequestBuilder
    {
        if (!$this->supportsRequestData($requestData)) {
            throw new InvalidArgumentException(get_class($requestData) . ' is not supported by ' . self::class);
        }

        $this->_initRequest($requestData);

        $head = $this->_getRequestHead($requestData);
        $content = $this->_getRequestContent($requestData);

        try {
            $requestBuilder = new RequestBuilder($requestData->getProfileConfig()->isSandbox());
            $requestBuilder = $requestBuilder->__call('call' . $this->_operation, $content !== null ? [$head, $content] : [$head]);
            if ($this->_subType) {
                $requestBuilder = $requestBuilder->subtype($this->_subType);
            }
        } catch (ExceptionAbstract $exception) {
            $this->eventDispatcher->dispatch(new RequestBuilderFailedEvent($exception, $requestData));
            throw new RatepayException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->eventDispatcher->dispatch(new RequestDoneEvent($requestData, $requestBuilder));

        $eventName = $requestBuilder->getResponse()->isSuccessful() ? self::EVENT_SUCCESSFUL : self::EVENT_FAILED;
        $this->eventDispatcher->dispatch(new ResponseEvent(
            $requestData->getContext(),
            $requestBuilder,
            $requestData
        ), static::class . $eventName);

        return $requestBuilder;
    }

    abstract protected function supportsRequestData(AbstractRequestData $requestData): bool;

    protected function getProfileConfig(AbstractRequestData $requestData): ?ProfileConfigEntity
    {
        return $requestData->getProfileConfig();
    }

    protected function getRequestHead(AbstractRequestData $requestData): Head
    {
        return $this->headFactory->getData($requestData);
    }

    protected function getRequestContent(AbstractRequestData $requestData): ?Content
    {
        return null;
    }

    protected function initRequest(AbstractRequestData $requestData): void
    {
    }

    private function _getRequestHead(AbstractRequestData $requestData): Head
    {
        $head = $this->getRequestHead($requestData);

        /** @var BuildEvent $event */
        $event = $this->eventDispatcher->dispatch(new BuildEvent($requestData, $head), static::class . self::EVENT_BUILD_HEAD);

        return $event->getBuildData();
    }

    private function _initRequest(AbstractRequestData $requestData): void
    {
        // set profile config to $requestData object in case it differs to the given object (or it was null)
        $requestData->setProfileConfig($this->getProfileConfig($requestData));

        if ($requestData->getProfileConfig() === null) {
            throw new ProfileNotFoundException();
        }

        $this->initRequest($requestData);
        $this->eventDispatcher->dispatch(new InitEvent($requestData), static::class . self::EVENT_INIT_REQUEST);
    }

    private function _getRequestContent(AbstractRequestData $requestData): ?Content
    {
        $content = $this->getRequestContent($requestData);

        /** @var BuildEvent $event */
        $event = $this->eventDispatcher->dispatch(new BuildEvent($requestData, $content), static::class . self::EVENT_BUILD_CONTENT);

        return $event->getBuildData();
    }
}
