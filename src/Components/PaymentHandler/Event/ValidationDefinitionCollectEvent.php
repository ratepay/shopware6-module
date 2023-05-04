<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler\Event;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Validator\Constraint;

class ValidationDefinitionCollectEvent
{
    public function __construct(
        // rector note: $definitions is protected cause with that rector will not change the property to readonly.
        // the property needs to be writeable, because we are using reference on this property
        protected array $definitions,
        private readonly DataBag $requestDataBag,
        private readonly OrderEntity|SalesChannelContext $baseData
    ) {
    }

    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * @param array<Constraint>|Constraint $constraint
     */
    public function addDefinition(string $field, array|Constraint $constraint): self
    {
        $path = explode('/', $field);
        array_pop($path);

        $currentNode = &$this->definitions;
        foreach ($path as $nodeKey) {
            $currentNode = &$currentNode[$nodeKey];
        }

        $currentNode[$field] = $constraint;

        return $this;
    }

    public function getBaseData(): OrderEntity|SalesChannelContext
    {
        return $this->baseData;
    }

    public function getRequestDataBag(): DataBag
    {
        return $this->requestDataBag;
    }
}
