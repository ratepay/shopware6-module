<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Mock\Repository;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\Salutation\SalutationEntity;

class SalutationRepositoryMock extends EntityRepositoryMock
{

    const MRS = ['mrs', 'Frau'];
    const MR = ['mr', 'Herr'];
    private array $elements;

    public function __construct(array $elements = [self::MRS, self::MR])
    {
        $this->elements = $elements;
    }

    public function search(Criteria $criteria, Context $context): EntitySearchResult
    {
        $entities = [];
        foreach ($this->elements as [$key, $name]) {
            $entities[] = $salutation = new SalutationEntity();
            $salutation->setId('123');
            $salutation->setUniqueIdentifier($salutation->getId());
            $salutation->setDisplayName($name);
            $salutation->setLetterName('letter-name');
            $salutation->setSalutationKey($key);
        }

        return $this->createEntitySearchResult($entities);
    }
}
