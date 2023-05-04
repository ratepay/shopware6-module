<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RedirectException\Exception;

use Exception;

class ForwardException extends Exception
{
    private ?string $customerMessage = null;

    public function __construct(
        private readonly string $route,
        private readonly array $routeParams = [],
        private readonly array $queryParams = [],
        Exception $exception = null
    ) {
        parent::__construct('', 0, $exception);
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getCustomerMessage(): ?string
    {
        return $this->customerMessage;
    }

    public function setCustomerMessage(?string $customerMessage): self
    {
        $this->customerMessage = $customerMessage;

        return $this;
    }
}
