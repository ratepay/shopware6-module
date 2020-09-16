<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service\Request;

use RatePAY\Exception\ExceptionAbstract;
use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use RatePAY\RequestBuilder;
use Ratepay\RpayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundException;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Event\BuildEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Event\RequestBuilderFailedEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Event\RequestDoneEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Event\ResponseEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Exception\RatepayException;
use Shopware\Core\Framework\Context;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractRequest
{
    protected const EVENT_SUCCESSFUL = '.successful';

    protected const EVENT_FAILED = '.failed';

    protected const EVENT_BUILD_HEAD = '.build.head';

    public const CALL_PAYMENT_REQUEST = 'PaymentRequest';

    public const CALL_DELIVER = 'ConfirmationDeliver';

    public const CALL_CHANGE = 'PaymentChange';

    public const CALL_PROFILE_REQUEST = 'ProfileRequest';

    /**
     * @var ConfigService
     */
    protected $configService;

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
        ConfigService $configService,
        HeadFactory $headFactory
    ) {
        $this->configService = $configService;
        $this->headFactory = $headFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws RatepayException
     */
    final public function doRequest(Context $context, IRequestData $requestData): RequestBuilder
    {
        $profileConfig = $this->getProfileConfig($context, $requestData);
        if ($profileConfig === null) {
            throw new ProfileNotFoundException();
        }

        $head = $this->getRequestHead($requestData, $profileConfig);
        $content = $this->getRequestContent($requestData);

        try {
            $requestBuilder = new RequestBuilder($profileConfig->isSandbox());
            $requestBuilder = $requestBuilder->__call('call' . $this->_operation, $content ? [$head, $content] : [$head]);
            if ($this->_subType) {
                $requestBuilder = $requestBuilder->subtype($this->_subType);
            }
        } catch (ExceptionAbstract $e) {
            $this->eventDispatcher->dispatch(new RequestBuilderFailedEvent($e, $requestData));
            throw new RatepayException($e->getMessage(), $e->getCode(), $e);
        }

        $this->eventDispatcher->dispatch(new RequestDoneEvent($context, $requestData, $requestBuilder));

        $requestEvent = new ResponseEvent($context, $requestBuilder, $requestData);
        if ($requestBuilder->getResponse()->isSuccessful()) {
            $this->eventDispatcher->dispatch($requestEvent, get_class($this) . self::EVENT_SUCCESSFUL);
        } else {
            $this->eventDispatcher->dispatch($requestEvent, get_class($this) . self::EVENT_FAILED);
        }

        return $requestBuilder;
    }

    abstract protected function getProfileConfig(Context $context, IRequestData $requestData): ProfileConfigEntity;

    protected function getRequestHead(IRequestData $requestData, ProfileConfigEntity $profileConfig): Head
    {
        $head = $this->headFactory->getData($requestData);
        $head->setCredential(
            (new Head\Credential())
                ->setProfileId($profileConfig->getProfileId())
                ->setSecuritycode($profileConfig->getSecurityCode())
        );
        /** @var BuildEvent $event */
        $event = $this->eventDispatcher->dispatch(new BuildEvent($requestData, $head), get_class($this) . self::EVENT_BUILD_HEAD);

        return $event->getBuildData();
    }

    /**
     * @return Content
     */
    protected function getRequestContent(IRequestData $requestData): ?Content
    {
        return null;
    }
}
