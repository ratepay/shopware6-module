<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Service\Request;


use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RatepayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RatepayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractModifyRequest extends AbstractOrderOperationRequest
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

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ConfigService $configService,
        HeadFactory $headFactory,
        EntityRepositoryInterface $profileConfigRepository,
        ShoppingBasketFactory $shoppingBasketFactory
    )
    {
        parent::__construct($eventDispatcher, $configService, $headFactory, $profileConfigRepository);
        $this->shoppingBasketFactory = $shoppingBasketFactory;
    }

    protected function getRequestHead(IRequestData $requestData, ProfileConfigEntity $profileConfig): Head
    {
        /** @var OrderOperationData $requestData */
        $head = parent::getRequestHead($requestData, $profileConfig);
        $head->setExternal($head->getExternal() ?: new Head\External());
        $head->getExternal()->setOrderId($requestData->getOrder()->getOrderNumber());
        $head->setTransactionId($requestData->getOrder()->getCustomFields()['ratepay']['transaction_id']);
        return $head;
    }

    protected function getRequestContent(IRequestData $requestData): ?Content
    {
        /** @var OrderOperationData $requestData */
        $content = new Content();
        $content->setShoppingBasket($this->shoppingBasketFactory->getData($requestData));
        return $content;
    }

}
