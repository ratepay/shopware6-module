<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service\Request;

use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ExternalFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractModifyRequest extends AbstractRequest
{
    protected string $_operation = self::CALL_CHANGE;

    protected EntityRepository $productRepository;

    protected EntityRepository $orderRepository;

    private ShoppingBasketFactory $shoppingBasketFactory;

    private EntityRepository $profileConfigRepository;

    private ExternalFactory $externalFactory;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HeadFactory $headFactory,
        EntityRepository $profileConfigRepository,
        ShoppingBasketFactory $shoppingBasketFactory,
        ExternalFactory $externalFactory
    ) {
        parent::__construct($eventDispatcher, $headFactory);
        $this->shoppingBasketFactory = $shoppingBasketFactory;
        $this->profileConfigRepository = $profileConfigRepository;
        $this->externalFactory = $externalFactory;
    }

    protected function getProfileConfig(AbstractRequestData $requestData): ?ProfileConfigEntity
    {
        /** @var OrderOperationData $requestData */

        /** @var RatepayOrderDataEntity $extension */
        $extension = $requestData->getOrder()->getExtension(OrderExtension::EXTENSION_NAME);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter(ProfileConfigEntity::FIELD_PROFILE_ID, $extension->getProfileId()));

        return $this->profileConfigRepository->search($criteria, $requestData->getContext())->first();
    }

    protected function getRequestHead(AbstractRequestData $requestData): Head
    {
        /** @var OrderOperationData $requestData */
        $head = parent::getRequestHead($requestData);
        $head->setExternal($this->externalFactory->getData($requestData));

        return $head;
    }

    protected function getRequestContent(AbstractRequestData $requestData): ?Content
    {
        /** @var OrderOperationData $requestData */
        $content = new Content();
        $content->setShoppingBasket($this->shoppingBasketFactory->getData($requestData));

        return $content;
    }

    protected function supportsRequestData(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof OrderOperationData;
    }
}
