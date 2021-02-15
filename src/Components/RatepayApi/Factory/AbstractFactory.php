<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use InvalidArgumentException;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Event\BuildEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractFactory
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    abstract protected function isSupported(AbstractRequestData $requestData): bool;

    final public function getData(AbstractRequestData $requestData): ?object
    {
        if (!$this->isSupported($requestData)) {
            throw new InvalidArgumentException(get_class($requestData) . ' is no supported by ' . self::class);
        }

        $data = $this->_getData($requestData);
        if ($data) {
            /** @var BuildEvent $event */
            $event = $this->eventDispatcher->dispatch(new BuildEvent($requestData, $data), get_class($this));
            $data = $event->getBuildData();
        }

        return $data;
    }

    abstract protected function _getData(AbstractRequestData $requestData): ?object;
}
