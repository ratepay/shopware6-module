<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use InvalidArgumentException;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Event\BuildEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractFactory
{
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    final public function getData(AbstractRequestData $requestData): ?object
    {
        if (!$this->isSupported($requestData)) {
            throw new InvalidArgumentException(get_class($requestData) . ' is no supported by ' . self::class);
        }

        $data = $this->_getData($requestData);
        if ($data !== null) {
            /** @var BuildEvent $event */
            $event = $this->eventDispatcher->dispatch(new BuildEvent($requestData, $data), static::class);
            $data = $event->getBuildData();
        }

        return $data;
    }

    abstract protected function isSupported(AbstractRequestData $requestData): bool;

    abstract protected function _getData(AbstractRequestData $requestData): ?object;
}
