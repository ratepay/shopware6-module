<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Subscriber;

use Exception;
use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\Logging\Model\ApiRequestLogEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use Ratepay\RpayPayments\Components\RatepayApi\Util\ResponseConverter;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Storefront\Page\Checkout\Finish\CheckoutFinishPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class PaymentFailedSubscriber implements EventSubscriberInterface
{
    private EntityRepository $ratepayApiLogRepository;

    public function __construct(
        EntityRepository $ratepayApiLogRepository
    )
    {
        $this->ratepayApiLogRepository = $ratepayApiLogRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutFinishPageLoadedEvent::class => 'onFinishPage',
        ];
    }

    public function onFinishPage(CheckoutFinishPageLoadedEvent $event): void
    {
        if (!$event->getPage()->isPaymentFailed()) {
            return;
        }

        $order = $event->getPage()->getOrder();
        $ratepayData = $order->getExtension(OrderExtension::EXTENSION_NAME);
        if ($ratepayData instanceof RatepayOrderDataEntity && !$ratepayData->isSuccessful()) {
            $transactionId = $ratepayData->getTransactionId();

            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter(ApiRequestLogEntity::FIELD_OPERATION, 'PAYMENT_REQUEST'));
            $criteria->addFilter(new EqualsFilter(ApiRequestLogEntity::FIELD_ADDITIONAL_DATA . '.transactionId', $transactionId));
            $criteria->addFilter(new EqualsFilter(ApiRequestLogEntity::FIELD_ADDITIONAL_DATA . '.orderNumber', $order->getOrderNumber()));
            $criteria->setLimit(1);

            $logEntry = $this->ratepayApiLogRepository->search($criteria, $event->getContext())->first();

            if (!$logEntry instanceof ApiRequestLogEntity) {
                // log entry was not found.
                return;
            }

            try {
                $response = ResponseConverter::getResponseObjectByXml(
                    PaymentRequestService::CALL_PAYMENT_REQUEST,
                    $logEntry->getResponse()
                );
            } catch (Exception $exception) {
                // response can not be converted.
                return;
            }

            $session = $event->getRequest()->getSession();
            if ($session instanceof Session) {
                $message = null;
                if (method_exists($response, 'getCustomerMessage')) {
                    $message = $response->getCustomerMessage();
                }

                $session->getFlashBag()->add('danger', !empty($message) ? $message : $response->getResultMessage());
            }
        }
    }
}
