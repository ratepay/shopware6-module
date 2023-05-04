<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\AdminOrders\Model;

use DateTimeInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class RatepayAdminOrderTokenEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    final public const FIELD_ID = 'id';

    /**
     * @var string
     */
    final public const FIELD_TOKEN = 'token';

    /**
     * @var string
     */
    final public const FIELD_SALES_CHANNEL_ID = 'salesChannelId';

    /**
     * @var string
     */
    final public const FIELD_SALES_CHANNEL_DOMAIN_ID = 'salesChannelDomainId';

    /**
     * @var string
     */
    final public const FIELD_CART_TOKEN = 'cartToken';

    /**
     * @var string
     */
    final public const FIELD_VAlID_UNTIL = 'validUntil';

    /**
     * @var string
     */
    final public const FIELD_CREATED_AT = 'createdAt';

    protected string $token;

    protected string $salesChannelId;

    protected string $salesChannelDomainId;

    protected string $cartToken;

    protected DateTimeInterface $validUntil;

    public function getToken(): string
    {
        return $this->token;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    public function getSalesChannelDomainId(): string
    {
        return $this->salesChannelDomainId;
    }

    public function getCartToken(): string
    {
        return $this->cartToken;
    }

    public function getValidUntil(): DateTimeInterface
    {
        return $this->validUntil;
    }
}
