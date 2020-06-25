<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Service\Request;


use Exception;
use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\BuildEvent;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\RequestBuilderFailedEvent;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\RequestDoneEvent;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\ResponseEvent;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RatepayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RatepayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use RatePAY\RequestBuilder;
use Shopware\Core\Framework\Context;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractRequest
{

    protected const EVENT_SUCCESSFUL = '.successful';
    protected const EVENT_FAILED = '.failed';
    protected const EVENT_BUILD_HEAD = '.build.head';

    const CALL_PAYMENT_REQUEST = "PaymentRequest";
    const CALL_DELIVER = "ConfirmationDeliver";
    const CALL_CHANGE = "PaymentChange";
    const CALL_PROFILE_REQUEST = "ProfileRequest";

    /**
     * @var ConfigService
     */
    protected $configService;

    /**
     * @var string
     * @deprecated
     */
    protected $_operation = null;

    /**
     * @var string
     * @deprecated
     */
    protected $_subType = null;

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
    )
    {
        $this->configService = $configService;
        $this->headFactory = $headFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Context $context
     * @param IRequestData $requestData
     * @return RequestBuilder
     */
    public final function doRequest(Context $context, IRequestData $requestData)
    {
        $profileConfig = $this->getProfileConfig($context, $requestData);
        if ($profileConfig == null) {
            throw new Exception('Transaction can not performed, cause no profile was found.');
        }

        $head = $this->getRequestHead($requestData, $profileConfig);
        $content = $this->getRequestContent($requestData);

        try {
            $requestBuilder = new RequestBuilder($profileConfig->isSandbox());
            $requestBuilder = $requestBuilder->__call('call' . $this->_operation, $content ? [$head, $content] : [$head]);
            if ($this->_subType) {
                $requestBuilder = $requestBuilder->subtype($this->_subType);
            }
        } catch (Exception $e) {
            $this->eventDispatcher->dispatch(new RequestBuilderFailedEvent($e, $requestData));
            throw $e;
        }

        $this->eventDispatcher->dispatch(new RequestDoneEvent($requestBuilder));

        $requestEvent = new ResponseEvent($context, $requestBuilder, $requestData);
        if ($requestBuilder->getResponse()->isSuccessful()) {
            $this->eventDispatcher->dispatch($requestEvent, get_class($this) . self::EVENT_SUCCESSFUL);
        } else {
            $this->eventDispatcher->dispatch($requestEvent, get_class($this) . self::EVENT_FAILED);
        }

        return $requestBuilder;
    }

    /**
     * @param Context $context
     * @param IRequestData $requestData
     * @return ProfileConfigEntity
     */
    abstract protected function getProfileConfig(Context $context, IRequestData $requestData);

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
     * @param IRequestData $requestData
     * @return Content
     */
    protected function getRequestContent(IRequestData $requestData): ?Content
    {
        return null;
    }
}
