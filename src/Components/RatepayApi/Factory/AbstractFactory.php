<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;


use Ratepay\RpayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Event\BuildEvent;
use Symfony\Component\HttpFoundation\Request;
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

    public function __construct(EventDispatcherInterface $eventDispatcher, RequestStack $requestStack)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->requestStack = $requestStack;
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

    protected function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }
}
