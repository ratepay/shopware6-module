<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentLock\Model;

use DateTime;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PaymentLockEntity extends Entity
{
    use EntityIdTrait;

    public const FIELD_ID = 'id';

    public const FIELD_CUSTOMER_ID = 'customerId';

    public const FIELD_PAYMENT_METHOD_ID = 'paymentMethodId';

    public const FIELD_LOCKED_UNTIL = 'locketUntil';

    /**
     * @var string
     */
    private $customerId;

    /**
     * @var string
     */
    private $paymentMethodId;

    /**
     * @var DateTime
     */
    private $lockedUntil;

    public function getId(): string
    {
        return $this->id;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getPaymentMethodId(): string
    {
        return $this->paymentMethodId;
    }

    public function getLockedUntil(): DateTime
    {
        return $this->lockedUntil;
    }
}
