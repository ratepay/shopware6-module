<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Model;

use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ProfileConfigMethodEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    final public const FIELD_ID = 'id';

    /**
     * @var string
     */
    final public const FIELD_PROFILE = 'profile';

    /**
     * @var string
     */
    final public const FIELD_PROFILE_ID = 'profileId';

    /**
     * @var string
     */
    final public const FIELD_PAYMENT_METHOD = 'paymentMethod';

    /**
     * @var string
     */
    final public const FIELD_PAYMENT_METHOD_ID = 'paymentMethodId';

    /**
     * @var string
     */
    final public const FIELD_LIMIT_MIN = 'limitMin';

    /**
     * @var string
     */
    final public const FIELD_LIMIT_MAX = 'limitMax';

    /**
     * @var string
     */
    final public const FIELD_LIMIT_MAX_B2B = 'limitMaxB2b';

    /**
     * @var string
     */
    final public const FIELD_ALLOW_DIFFERENT_ADDRESSES = 'allowDifferentAddresses';

    /**
     * @var string
     */
    final public const FIELD_ALLOW_B2B = 'allowB2b';

    /**
     * @var string
     */
    final public const FIELD_INSTALLMENT_CONFIG = 'installmentConfig';

    protected ?PaymentMethodEntity $paymentMethod = null;

    protected string $paymentMethodId;

    protected ?ProfileConfigEntity $profile = null;

    protected string $profileId;

    protected ?float $limitMin = null;

    protected ?float $limitMax = null;

    protected ?float $limitMaxB2b = null;

    protected bool $allowDifferentAddresses;

    protected ?bool $allowB2b = null;

    protected ?ProfileConfigMethodInstallmentEntity $installmentConfig = null;

    public function getPaymentMethod(): ?PaymentMethodEntity
    {
        return $this->paymentMethod;
    }

    public function getPaymentMethodId(): string
    {
        return $this->paymentMethodId;
    }

    public function getProfile(): ProfileConfigEntity
    {
        return $this->profile;
    }

    public function getProfileId(): string
    {
        return $this->profileId;
    }

    public function getLimitMin(): ?float
    {
        return $this->limitMin;
    }

    public function getLimitMax(): ?float
    {
        return $this->limitMax;
    }

    public function getLimitMaxB2b(): ?float
    {
        return $this->limitMaxB2b;
    }

    public function isAllowDifferentAddresses(): bool
    {
        return $this->allowDifferentAddresses;
    }

    public function isAllowB2b(): ?bool
    {
        return $this->allowB2b;
    }

    public function getInstallmentConfig(): ?ProfileConfigMethodInstallmentEntity
    {
        return $this->installmentConfig;
    }
}
