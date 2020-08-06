<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentLock\Model;

use DateTime;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PaymentLockEntity extends Entity
{

    public const FIELD_ID = 'id';
    public const FIELD_CUSTOMER_ID = 'customerId';
    public const FIELD_PAYMENT_METHOD_ID = 'paymentMethodId';
    public const FIELD_LOCKED_UNTIL = 'locketUntil';

    use EntityIdTrait;

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

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * @return string
     */
    public function getPaymentMethodId(): string
    {
        return $this->paymentMethodId;
    }

    /**
     * @return DateTime
     */
    public function getLockedUntil(): DateTime
    {
        return $this->lockedUntil;
    }

}
