<?php

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
    private string $route;

    private array $routeParams;

    private array $queryParams;

    private ?string $customerMessage = null;

    public function __construct(string $route, array $routeParams = [], array $query = [], Exception $exception = null)
    {
        parent::__construct('', 0, $exception);
        $this->route = $route;
        $this->routeParams = $routeParams;
        $this->queryParams = $query;
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
