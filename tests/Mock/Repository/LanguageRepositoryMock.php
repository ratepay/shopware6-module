<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Mock\Repository;

use Ratepay\RpayPayments\Tests\Mock\Model\LanguageMock;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

class LanguageRepositoryMock extends EntityRepositoryMock
{
    public function search(Criteria $criteria, Context $context): EntitySearchResult
    {
        return $this->createEntitySearchResult([
            LanguageMock::createMock('de', 'de-de')
        ]);
    }
}
