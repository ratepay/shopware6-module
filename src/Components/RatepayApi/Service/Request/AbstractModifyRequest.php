<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service\Request;

use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundException;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractModifyRequest extends AbstractRequest
{
    protected $_operation = self::CALL_CHANGE;

    /**
     * @var array
     */
    protected $items;

    /**
     * @var EntityRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var EntityRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var ShoppingBasketFactory
     */
    private $shoppingBasketFactory;

    /**
     * @var EntityRepositoryInterface
     */
    private $profileConfigRepository;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HeadFactory $headFactory,
        EntityRepositoryInterface $profileConfigRepository,
        ShoppingBasketFactory $shoppingBasketFactory
    ) {
        parent::__construct($eventDispatcher, $headFactory);
        $this->shoppingBasketFactory = $shoppingBasketFactory;
        $this->profileConfigRepository = $profileConfigRepository;
    }

    protected function getProfileConfig(AbstractRequestData $requestData): ProfileConfigEntity
    {
        /** @var OrderOperationData $requestData */

        /** @var RatepayOrderDataEntity $extension */
        $extension = $requestData->getOrder()->getExtension(OrderExtension::EXTENSION_NAME);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter(
            ProfileConfigEntity::FIELD_PROFILE_ID,
            $extension->getProfileId()
        ));

        $profileConfig = $this->profileConfigRepository->search($criteria, $requestData->getContext())->first();
        if ($profileConfig === null) {
            throw new ProfileNotFoundException();
        }

        return $profileConfig;
    }

    protected function getRequestHead(AbstractRequestData $requestData): Head
    {
        /** @var OrderOperationData $requestData */
        $head = parent::getRequestHead($requestData);
        $head->setExternal($head->getExternal() ?: new Head\External());
        $head->getExternal()->setOrderId($requestData->getOrder()->getOrderNumber());

        /** @var RatepayOrderDataEntity $orderExtension */
        $orderExtension = $requestData->getOrder()->getExtension(OrderExtension::EXTENSION_NAME);
        $head->setTransactionId($orderExtension->getTransactionId());

        return $head;
    }

    protected function getRequestContent(AbstractRequestData $requestData): ?Content
    {
        /** @var OrderOperationData $requestData */
        $content = new Content();
        $content->setShoppingBasket($this->shoppingBasketFactory->getData($requestData));

        return $content;
    }
}
