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
use RatePAY\Model\Request\SubModel\AbstractModel;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Event\BuildEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @template ReturnType of AbstractModel|null
 */
abstract class AbstractFactory
{
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @return ReturnType
     */
    final public function getData(AbstractRequestData $requestData): ?object
    {
        if (!$this->isSupported($requestData)) {
            throw new InvalidArgumentException($requestData::class . ' is no supported by ' . self::class);
        }

        $data = $this->_getData($requestData);
        if ($data !== null) {
            $event = new BuildEvent($requestData, $data);
            $this->eventDispatcher->dispatch($event, static::class);
            $data = $event->getBuildData();
        }

        return $data;
    }

    abstract protected function isSupported(AbstractRequestData $requestData): bool;

    /**
     * @return ReturnType
     */
    abstract protected function _getData(AbstractRequestData $requestData): ?object;
}
