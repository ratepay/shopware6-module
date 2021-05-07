<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Model;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class TransactionIdEntity extends Entity
{
    use EntityIdTrait;

    public const FIELD_ID = 'id';

    public const FIELD_IDENTIFIER = 'identifier';

    public const FIELD_PROFILE = 'profile';

    public const FIELD_PROFILE_ID = 'profileId';

    public const FIELD_TRANSACTION_ID = 'transactionId';

    public const FIELD_CREATED_AT = 'created_at';

    protected ?string $identifier = null;

    protected ?string $transactionId = null;

    protected ?string $profileId = null;

    protected ?ProfileConfigEntity $profile = null;

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function getProfile(): ?ProfileConfigEntity
    {
        return $this->profile;
    }

    public function getProfileId(): ?string
    {
        return $this->profileId;
    }
}
