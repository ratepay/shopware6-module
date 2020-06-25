<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Factory;


use Ratepay\RatepayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\BuildEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractFactory
{

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getData(IRequestData $requestData): ?object
    {
        $data = $this->_getData($requestData);
        if ($data) {
            /** @var BuildEvent $event */
            $event = $this->eventDispatcher->dispatch(new BuildEvent($requestData, $data), get_class($this));
            $data = $event->getBuildData();
        }
        return $data;
    }

    abstract protected function _getData(IRequestData $requestData): ?object;
}
