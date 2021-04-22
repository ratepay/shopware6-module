<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\AdminOrders\Model;

use DateTime;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class RatepayAdminOrderTokenEntity extends Entity
{
    public const FIELD_ID = 'id';

    public const FIELD_TOKEN = 'token';

    public const FIELD_SALES_CHANNEL_ID = 'salesChannelId';

    public const FIELD_SALES_CHANNEL_DOMAIN_ID = 'salesChannelDomainId';

    public const FIELD_CART_TOKEN = 'cartToken';

    public const FIELD_VAlID_UNTIL = 'validUntil';

    public const FIELD_CREATED_AT = 'createdAt';

    use EntityIdTrait;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $salesChannelId;

    /**
     * @var string
     */
    protected $salesChannelDomainId;

    /**
     * @var string
     */
    protected $cartToken;

    /**
     * @var DateTime
     */
    protected $validUntil;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    /**
     * @return string
     */
    public function getSalesChannelDomainId(): string
    {
        return $this->salesChannelDomainId;
    }

    /**
     * @return string
     */
    public function getCartToken(): string
    {
        return $this->cartToken;
    }

    /**
     * @return DateTime
     */
    public function getValidUntil(): DateTime
    {
        return $this->validUntil;
    }
}
