<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\InstallmentCalculator\Subscriber;


use RatePAY\Model\Request\SubModel\Content\Payment;
use Ratepay\RatepayPayments\Components\InstallmentCalculator\Service\InstallmentService;
use Ratepay\RatepayPayments\Components\InstallmentCalculator\Util\PlanHasher;
use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\BuildEvent;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\PaymentFactory;
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
            PaymentFactory::class => 'buildPayment'
        ];
    }

    public function buildPayment(BuildEvent $event)
    {
        /** @var PaymentRequestData $requestData */
        $requestData = $event->getRequestData();

        if ($requestData->getTransaction()->getPaymentMethod()->getHandlerIdentifier() === InstallmentPaymentHandler::class) {
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
                throw new \Exception('the hash value of the calculated plan does not match the given hash');
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
}
