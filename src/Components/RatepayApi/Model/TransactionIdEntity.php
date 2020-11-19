<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Model;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class TransactionIdEntity extends Entity
{
    public const FIELD_ID = 'id';

    public const FIELD_IDENTIFIER = 'identifier';

    public const FIELD_TRANSACTION_ID = 'transactionId';

    use EntityIdTrait;

    /** @var string|null */
    protected $identifier;

    /** @var string|null */
    protected $transactionId;

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }
}
