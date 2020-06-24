<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


use Ratepay\RatepayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RatepayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigEntity;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigMethodEntity;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigRepository;
use RatePAY\RequestBuilder;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method RequestBuilder doRequest(Context $context, OrderOperationData $requestData)
 */
abstract class AbstractOrderOperationRequest extends AbstractRequest
{

    /**
     * @var ProfileConfigRepository
     */
    private $profileConfigRepository;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ConfigService $configService,
        HeadFactory $headFactory,
        ProfileConfigRepository $profileConfigRepository
    )
    {
        parent::__construct($eventDispatcher, $configService, $headFactory);
        $this->profileConfigRepository = $profileConfigRepository;
    }

    protected function getProfileConfig(Context $context, IRequestData $requestData)
    {
        /** @var OrderOperationData $requestData */

        $criteria = new Criteria();
        $criteria->addAssociation(ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS);

        // payment method
        $criteria->addFilter(new EqualsFilter(
            ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_PAYMENT_METHOD_ID,
            $requestData->getTransaction()->getPaymentMethod()->getId()
        ));

        // billing country
        $criteria->addFilter(new EqualsFilter(
            ProfileConfigEntity::FIELD_COUNTRY_CODE_BILLING,
            $requestData->getOrder()->getAddresses()->get($requestData->getOrder()->getBillingAddressId())->getCountry()->getIso()
        ));

        // delivery country
        if ($delivery = $requestData->getOrder()->getDeliveries()->first()) {
            $criteria->addFilter(new EqualsFilter(
                ProfileConfigEntity::FIELD_COUNTRY_CODE_SHIPPING,
                $delivery->getShippingOrderAddress()->getCountry()->getIso()
            ));
        }

        // sales channel
        $criteria->addFilter(new EqualsFilter(
            ProfileConfigEntity::FIELD_SALES_CHANNEL_ID,
            $requestData->getOrder()->getSalesChannelId()
        ));

        // currency
        $criteria->addFilter(new EqualsFilter(
            ProfileConfigEntity::FIELD_CURRENCY,
            $requestData->getOrder()->getCurrency()->getIsoCode()
        ));

        // status
        $criteria->addFilter(new EqualsFilter(
            ProfileConfigEntity::FIELD_STATUS,
            true
        ));

        return $this->profileConfigRepository->search($criteria, $context)->first();
    }

}
