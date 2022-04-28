<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Mock\Repository;

use Ratepay\RpayPayments\Tests\Mock\Model\PaymentMethodMock;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;

class PaymentMethodRepositoryMock extends EntityRepositoryMock
{
    /**
     * @var PaymentMethodEntity[]
     */
    private array $methods;

    /**
     * @param string[] $methods
     */
    public function __construct(array $methods)
    {
        $this->methods = PaymentMethodMock::createArray($methods);
    }

    public function searchIds(Criteria $criteria, Context $context): IdSearchResult
    {
        $results = [];
        if (count($criteria->getIds())) {
            foreach ($criteria->getIds() as $id) {
                if (isset($this->methods[$id])) {
                    $results[$id] = $this->methods[$id];
                }
            }
        } else {
            $results = $this->methods;
        }

        return new IdSearchResult(count($results), $results, $criteria, $context);
    }

    public function search(Criteria $criteria, Context $context): EntitySearchResult
    {
        $results = [];
        if (count($criteria->getIds())) {
            foreach ($criteria->getIds() as $id) {
                if (isset($this->methods[$id])) {
                    $results[$id] = $this->methods[$id];
                }
            }
        } else {
            $results = $this->methods;
        }

        return new EntitySearchResult('payment_method', count($results), new EntityCollection($results), null, $criteria, $context);
    }
}
