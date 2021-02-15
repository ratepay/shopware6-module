<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
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
    protected const EVENT_SUCCESSFUL = '.successful';

    protected const EVENT_FAILED = '.failed';

    protected const EVENT_BUILD_HEAD = '.build.head';

    protected const EVENT_BUILD_CONTENT = '.build.content';

    protected const EVENT_INIT_REQUEST = '.init.request';

    public const CALL_PAYMENT_REQUEST = 'PaymentRequest';

    public const CALL_DELIVER = 'ConfirmationDeliver';

    public const CALL_CHANGE = 'PaymentChange';

    public const CALL_PROFILE_REQUEST = 'ProfileRequest';

    public const CALL_PAYMENT_QUERY = 'PaymentQuery';

    public const CALL_PAYMENT_INIT = 'PaymentInit';

    /**
     * @var string
     *
     * @deprecated
     */
    protected $_operation;

    /**
     * @var string
     *
     * @deprecated
     */
    protected $_subType;

    /**
     * @var HeadFactory
     */
    private $headFactory;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HeadFactory $headFactory
    ) {
        $this->headFactory = $headFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    abstract protected function supportsRequestData(AbstractRequestData $requestData): bool;

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
            $requestBuilder = $requestBuilder->__call('call' . $this->_operation, $content ? [$head, $content] : [$head]);
            if ($this->_subType) {
                $requestBuilder = $requestBuilder->subtype($this->_subType);
            }
        } catch (ExceptionAbstract $e) {
            $this->eventDispatcher->dispatch(new RequestBuilderFailedEvent($e, $requestData));
            throw new RatepayException($e->getMessage(), $e->getCode(), $e);
        }

        $this->eventDispatcher->dispatch(new RequestDoneEvent($requestData, $requestBuilder));

        $eventName = $requestBuilder->getResponse()->isSuccessful() ? self::EVENT_SUCCESSFUL : self::EVENT_FAILED;
        $this->eventDispatcher->dispatch(new ResponseEvent(
            $requestData->getContext(),
            $requestBuilder,
            $requestData
        ), get_class($this) . $eventName);

        return $requestBuilder;
    }

    protected function getProfileConfig(AbstractRequestData $requestData): ProfileConfigEntity
    {
        $profileConfig = $requestData->getProfileConfig();
        if ($profileConfig === null) {
            throw new ProfileNotFoundException();
        }

        return $profileConfig;
    }

    private function _getRequestHead(AbstractRequestData $requestData): Head
    {
        $head = $this->getRequestHead($requestData);

        /** @var BuildEvent $event */
        $event = $this->eventDispatcher->dispatch(new BuildEvent($requestData, $head), get_class($this) . self::EVENT_BUILD_HEAD);

        return $event->getBuildData();
    }

    private function _initRequest(AbstractRequestData $requestData): void
    {
        $requestData->setProfileConfig($this->getProfileConfig($requestData));

        $this->initRequest($requestData);
        /* @var BuildEvent $event */
        $this->eventDispatcher->dispatch(new InitEvent($requestData), get_class($this) . self::EVENT_INIT_REQUEST);
    }

    protected function getRequestHead(AbstractRequestData $requestData): Head
    {
        return $this->headFactory->getData($requestData);
    }

    private function _getRequestContent(AbstractRequestData $requestData): ?Content
    {
        $content = $this->getRequestContent($requestData);

        /** @var BuildEvent $event */
        $event = $this->eventDispatcher->dispatch(new BuildEvent($requestData, $content), get_class($this) . self::EVENT_BUILD_CONTENT);

        return $event->getBuildData();
    }

    protected function getRequestContent(AbstractRequestData $requestData): ?Content
    {
        return null;
    }

    protected function initRequest(AbstractRequestData $requestData): void
    {
    }
}
