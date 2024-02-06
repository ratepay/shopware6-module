<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RedirectException\Subscriber;

use Ratepay\RpayPayments\Components\RedirectException\Exception\ForwardException;
use Ratepay\RpayPayments\Components\RedirectException\Exception\RedirectException;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Payment\PaymentException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\RequestTransformerInterface;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class RedirectExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly OrderTransactionStateHandler $orderTransactionStateHandler
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 9000],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        $prevThrowable = $throwable->getPrevious();
        if ($prevThrowable instanceof PaymentException) {
            // set the transaction to failed. Without this, the customer will not be able to try a repayment.
            $this->orderTransactionStateHandler->fail($prevThrowable->getOrderTransactionId(), Context::createDefaultContext());
        }

        if ($throwable instanceof RedirectException) {
            $event->setResponse($throwable->getRedirectResponse());
        } elseif ($throwable instanceof ForwardException) {
            $routeScope = $event->getRequest()->attributes->get('_routeScope');
            if (!is_array($routeScope) || $routeScope[0] !== 'storefront') {
                // skip forwarding request, if it is a api request (e.g. headless)
                throw $throwable->getPrevious();
            }

            if ($throwable->getCustomerMessage() !== null && ($session = $event->getRequest()->getSession()) instanceof Session) {
                $session->getFlashBag()->add(StorefrontController::DANGER, $throwable->getCustomerMessage());
            }

            /** @var \Symfony\Component\Routing\Router $router */
            $router = $this->container->get('router');
            $url = $router->generate(
                $throwable->getRoute(),
                $throwable->getRouteParams(),
                Router::PATH_INFO
            );

            // for the route matching the request method is set to "GET" because
            // this method is not ought to be used as a post passthrough
            // rather it shall return templates or redirects to display results of the request ahead
            $method = $router->getContext()->getMethod();
            $router->getContext()->setMethod(Request::METHOD_GET);

            $route = $router->match($url);
            $router->getContext()->setMethod($method); // reset method

            $attributes = array_merge(
                $this->container->get(RequestTransformerInterface::class)->extractInheritableAttributes($event->getRequest()),
                $route,
                $throwable->getQueryParams(),
                [
                    '_route_params' => $throwable->getRouteParams(),
                ]
            );
            $subRequest = $event->getRequest()->duplicate($route, null, $attributes);

            $response = $this->container->get('http_kernel')
                ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
            $event->setResponse($response);
            $event->allowCustomResponseCode();
        }
    }
}
