<?php


namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


use Ratepay\RatepayPayments\Core\PluginConfig\Services\ConfigService;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigEntity;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigMethodEntity;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigRepository;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\RequestLogger;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\HeadFactory;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

abstract class AbstractOrderOperationRequest extends AbstractRequest
{

    /**
     * @var OrderEntity
     */
    protected $order;

    /**
     * @var OrderTransactionEntity
     */
    protected $transaction;

    /**
     * @var ProfileConfigRepository
     */
    private $profileConfigRepository;

    public function __construct(
        ConfigService $configService,
        RequestLogger $requestLogger,
        HeadFactory $headFactory,
        ProfileConfigRepository $profileConfigRepository
    )
    {
        parent::__construct($configService, $requestLogger, $headFactory);
        $this->profileConfigRepository = $profileConfigRepository;
    }

    protected function getProfileConfig()
    {
        $criteria = new Criteria();
        $criteria->addAssociation(ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS);

        // payment method
        $criteria->addFilter(new EqualsFilter(
            ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS.'.'.ProfileConfigMethodEntity::FIELD_PAYMENT_METHOD_ID,
            $this->transaction->getPaymentMethod()->getId()
        ));

        // billing country
        $criteria->addFilter(new EqualsFilter(
            ProfileConfigEntity::FIELD_COUNTRY_CODE_BILLING,
            $this->order->getAddresses()->get($this->order->getBillingAddressId())->getCountry()->getIso()
        ));

        // delivery country
        if($delivery = $this->order->getDeliveries()->first()) {
            $criteria->addFilter(new EqualsFilter(
                ProfileConfigEntity::FIELD_COUNTRY_CODE_SHIPPING,
                $delivery->getShippingOrderAddress()->getCountry()->getIso()
            ));
        }

        // sales channel
        $criteria->addFilter(new EqualsFilter(
            ProfileConfigEntity::FIELD_SALES_CHANNEL_ID,
            $this->order->getSalesChannelId()
        ));

        // currency
        $criteria->addFilter(new EqualsFilter(
            ProfileConfigEntity::FIELD_CURRENCY,
            $this->order->getCurrency()->getIsoCode()
        ));

        // currency
        $criteria->addFilter(new EqualsFilter(
            ProfileConfigEntity::FIELD_STATUS,
            true
        ));

        return $this->profileConfigRepository->search($criteria, Context::createDefaultContext())->first();
    }

}
