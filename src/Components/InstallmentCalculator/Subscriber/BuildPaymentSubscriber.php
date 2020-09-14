<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\InstallmentCalculator\Subscriber;


use Exception;
use RatePAY\Model\Request\SubModel\Content\Payment;
use RatePAY\Model\Request\SubModel\Content\ShoppingBasket;
use Ratepay\RatepayPayments\Components\InstallmentCalculator\Exception\DebitNotAllowedOnInstallment;
use Ratepay\RatepayPayments\Components\InstallmentCalculator\Service\InstallmentService;
use Ratepay\RatepayPayments\Components\InstallmentCalculator\Util\PlanHasher;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\AddCreditData;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\BuildEvent;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\PaymentFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RatepayPayments\Util\MethodHelper;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BuildPaymentSubscriber implements EventSubscriberInterface
{

    /**
     * @var InstallmentService
     */
    private $installmentService;

    public function __construct(InstallmentService $installmentService)
    {
        $this->installmentService = $installmentService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PaymentFactory::class => 'buildPayment',
            ShoppingBasketFactory::class => 'buildShoppingBasket'
        ];
    }

    public function buildPayment(BuildEvent $event)
    {
        /** @var PaymentRequestData $requestData */
        $requestData = $event->getRequestData();

        if (MethodHelper::isInstallmentMethod($requestData->getTransaction()->getPaymentMethod()->getHandlerIdentifier())) {
            /** @var Payment $paymentObject */
            $paymentObject = $event->getBuildData();

            /** @var RequestDataBag $requestedInstallment */
            $requestedInstallment = $requestData->getRequestDataBag()->get('ratepay')->get('installment');
            $plan = $this->installmentService->getInstallmentPlanData(
                $requestData->getSalesChannelContext(),
                $requestedInstallment->get('type'),
                $requestedInstallment->get('value'),
                $paymentObject->getAmount()
            );

            if (PlanHasher::isPlanEqualWithHash($requestedInstallment->get('hash'), $plan)) {
                throw new Exception('the hash value of the calculated plan does not match the given hash');
            }

            $paymentObject->setDebitPayType('')
                ->setAmount($plan['totalAmount'])
                ->setInstallmentDetails(
                    (new Payment\InstallmentDetails())
                        ->setInstallmentNumber($plan['numberOfRatesFull'])
                        ->setInstallmentAmount($plan['rate'])
                        ->setLastInstallmentAmount($plan['lastRate'])
                        ->setInterestRate($plan['interestRate'])
                        ->setPaymentFirstday($plan['paymentFirstday'])
                )
                ->setDebitPayType($requestedInstallment->get('paymentType'));
        }
    }

    public function buildShoppingBasket(BuildEvent $event): BuildEvent
    {
        /** @var OrderOperationData $requestData */
        $requestData = $event->getRequestData();
        $paymentMethod = $requestData->getTransaction()->getPaymentMethod();

        /** @var ShoppingBasket $basket */
        $basket = $event->getBuildData();
        if ($requestData instanceof AddCreditData &&
            MethodHelper::isInstallmentMethod($paymentMethod->getHandlerIdentifier())
        ) {
            $items = $basket->getItems()->admittedFields['Item']['value'];
            /** @var ShoppingBasket\Items\Item $item */
            foreach ($items as $item) {
                if ($item->getUnitPriceGross() > 0) {
                    throw new DebitNotAllowedOnInstallment();
                }
            }
        }
        return $event;
    }
}
