<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Subscriber;

use Exception;
use InvalidArgumentException;
use RatePAY\Model\Request\SubModel\Content\Payment;
use RatePAY\Model\Request\SubModel\Content\ShoppingBasket;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Exception\DebitNotAllowedOnInstallment;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Service\InstallmentService;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Util\PlanHasher;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AddCreditData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Event\BuildEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\PaymentFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RpayPayments\Util\MethodHelper;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BuildPaymentSubscriber implements EventSubscriberInterface
{
    private InstallmentService $installmentService;

    public function __construct(InstallmentService $installmentService)
    {
        $this->installmentService = $installmentService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentFactory::class => 'buildPayment',
            ShoppingBasketFactory::class => 'buildShoppingBasket',
        ];
    }

    public function buildPayment(BuildEvent $event): void
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

            $paymentType = $requestedInstallment->get('paymentType');
            switch ($paymentType) {
                case 'DIRECT-DEBIT':
                    $paymentFirstDay = 2;
                    break;
                case 'BANK-TRANSFER':
                    $paymentFirstDay = 28;
                    break;
                default:
                    throw new InvalidArgumentException('invalid paymentType');
            }

            $paymentObject
                ->setAmount($plan['totalAmount'])
                ->setInstallmentDetails(
                    (new Payment\InstallmentDetails())
                        ->setInstallmentNumber($plan['numberOfRatesFull'])
                        ->setInstallmentAmount($plan['rate'])
                        ->setLastInstallmentAmount($plan['lastRate'])
                        ->setInterestRate($plan['interestRate'])
                        ->setPaymentFirstday($paymentFirstDay)
                )
                ->setDebitPayType($paymentType);
        }
    }

    public function buildShoppingBasket(BuildEvent $event): BuildEvent
    {
        /** @var OrderOperationData $requestData */
        $requestData = $event->getRequestData();

        if ($requestData instanceof PaymentRequestData === false) {
            return $event;
        }

        $paymentMethod = $requestData->getTransaction()->getPaymentMethod();

        /** @var ShoppingBasket $basket */
        $basket = $event->getBuildData();
        if ($paymentMethod && $requestData instanceof AddCreditData &&
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
