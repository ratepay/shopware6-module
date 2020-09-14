<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Subscriber;


use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\Logging\Model\ApiRequestLogEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use Ratepay\RpayPayments\Components\RatepayApi\Util\ResponseConverter;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Storefront\Page\Checkout\Finish\CheckoutFinishPageLoadedEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentFailedSubscriber implements EventSubscriberInterface
{

    /**
     * @var EntityRepositoryInterface
     */
    private $ratepayApiLogRepository;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        EntityRepositoryInterface $ratepayApiLogRepository,
        ContainerInterface $container
    )
    {
        $this->ratepayApiLogRepository = $ratepayApiLogRepository;
        $this->container = $container;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutFinishPageLoadedEvent::class => 'onFinishPage'
        ];
    }

    public function onFinishPage(CheckoutFinishPageLoadedEvent $event): void
    {
        if ($event->getPage()->isPaymentFailed() === false) {
            return;
        }

        $order = $event->getPage()->getOrder();
        /** @var RatepayOrderDataEntity $ratepayData */
        $ratepayData = $order->getExtension(OrderExtension::EXTENSION_NAME);
        if ($ratepayData && $ratepayData->isSuccessful() === false) {
            $transactionId = $ratepayData->getTransactionId();

            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter(ApiRequestLogEntity::FIELD_OPERATION, 'PAYMENT_REQUEST'));
            $criteria->addFilter(new EqualsFilter(ApiRequestLogEntity::FIELD_ADDITIONAL_DATA . '.transactionId', $transactionId));
            $criteria->addFilter(new EqualsFilter(ApiRequestLogEntity::FIELD_ADDITIONAL_DATA . '.orderNumber', $order->getOrderNumber()));
            $criteria->setLimit(1);

            $logEntries = $this->ratepayApiLogRepository->search($criteria, $event->getContext());
            /** @var ApiRequestLogEntity $logEntry */
            $logEntry = $logEntries->first();

            if ($logEntry === null) {
                // log entry was not found.
                return;
            }

            try {
                $response = ResponseConverter::getResponseObjectByXml(
                    PaymentRequestService::CALL_PAYMENT_REQUEST,
                    $logEntry->getResponse()
                );
            } catch (\Exception $e) {
                // response can not be converted.
                return;
            }

            $session = $this->container->get('session');
            if ($response && $session) {
                $message = !empty($response->getCustomerMessage()) ? $response->getCustomerMessage() : $response->getResultMessage();
                $session->getFlashBag()->add('danger', $message);
            }
        }
    }
}
