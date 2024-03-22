<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\DeviceFingerprint\Subscriber;

use RatePAY\Model\Request\SubModel\Head;
use RatePAY\Model\Request\SubModel\Head\CustomerDevice;
use Ratepay\RpayPayments\Components\Checkout\Event\OrderExtensionDataBuilt;
use Ratepay\RpayPayments\Components\Checkout\Event\PaymentDataExtensionBuilt;
use Ratepay\RpayPayments\Components\DeviceFingerprint\Constraint\DfpConstraint;
use Ratepay\RpayPayments\Components\DeviceFingerprint\DfpServiceInterface;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\ValidationDefinitionCollectEvent;
use Ratepay\RpayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Event\BuildEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use Ratepay\RpayPayments\Core\Entity\Extension\OrderExtension;
use Ratepay\RpayPayments\Core\Entity\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Util\RequestHelper;
use Shopware\Storefront\Event\RouteRequest\OrderRouteRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\NotBlank;

class DeviceFingerprintSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly DfpServiceInterface $dfpService,
        private readonly ConfigService $configService,
        private readonly RequestStack $requestStack
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentDataExtensionBuilt::class => 'addRatepayTemplateData',
            PaymentRequestService::EVENT_BUILD_HEAD => 'onPaymentRequest',
            ValidationDefinitionCollectEvent::class => 'addValidationDefinition',
            OrderExtensionDataBuilt::class => 'addDfpTokenToOrder',
            OrderRouteRequestEvent::class => 'addRatepayDataToAccountOrderCriteria',
        ];
    }

    public function onPaymentRequest(BuildEvent $buildEvent): void
    {
        /** @var PaymentRequestData $requestData */
        $requestData = $buildEvent->getRequestData();
        /** @var Head $head */
        $head = $buildEvent->getBuildData();

        $ratepayData = RequestHelper::getRatepayData($requestData->getRequestDataBag());

        if ($token = $ratepayData->get('deviceIdentToken') ?? null) {
            $head->setCustomerDevice((new CustomerDevice())->setDeviceToken($token));
        }
    }

    public function addRatepayTemplateData(PaymentDataExtensionBuilt $event): void
    {
        $baseData = $event->getOrderEntity() ?? $event->getSalesChannelContext();

        $snippet = $this->dfpService->getDfpSnippet($this->requestStack->getCurrentRequest(), $baseData);
        if ($snippet) {
            $event->getExtension()->set('dfp', [
                'snippetId' => $this->configService->getDeviceFingerprintSnippetId(),
                'html' => $snippet,
                'deviceIdentToken' => $this->dfpService->generatedDfpId($this->requestStack->getCurrentRequest(), $baseData),
            ]);
        }
    }

    public function addValidationDefinition(ValidationDefinitionCollectEvent $event): void
    {
        if (!$this->dfpService->isDfpRequired($event->getBaseData())) {
            return;
        }

        $event->addDefinition('deviceIdentToken', [
            new NotBlank(),
            new DfpConstraint($this->dfpService, $event->getBaseData()),
        ]);
    }

    public function addDfpTokenToOrder(OrderExtensionDataBuilt $event): void
    {
        $requestDataBag = $event->getPaymentRequestData()->getRequestDataBag();
        $ratepayData = RequestHelper::getRatepayData($requestDataBag);

        $data = $event->getData();
        $data[RatepayOrderDataEntity::FIELD_ADDITIONAL_DATA]['deviceIdentToken'] = $ratepayData->get('deviceIdentToken');

        $event->setData($data);
    }

    public function addRatepayDataToAccountOrderCriteria(OrderRouteRequestEvent $event): void
    {
        $event->getCriteria()->addAssociation(OrderExtension::EXTENSION_NAME);
    }
}
