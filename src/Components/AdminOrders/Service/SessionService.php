<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\AdminOrders\Service;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionService
{
    public function __construct(
        private readonly string $sessionKey
    ) {
    }

    public function isAdminSession(SalesChannelContext $context, SessionInterface $session): bool
    {
        return $this->isRatepayAdminSession($session) || $this->isLoggedInAsCustomer($context, $session);
    }

    public function canLogout(SalesChannelContext $context, SessionInterface $session): bool
    {
        // allow only session logout if the session has been created by ratepay
        return $this->isRatepayAdminSession($session) && !$this->isLoggedInAsCustomer($context, $session);
    }

    public function isLoggedInAsCustomer(SalesChannelContext $context, SessionInterface $session): bool
    {
        // supported since SW 6.6.5.x - TODO remove this check if compatibility has been change to Shopware >= 6.6.5
        if (method_exists($context, 'getImitatingUserId') && $context->getImitatingUserId() !== null) {
            return true;
        }

        if ($context->getCustomerId() === null) {
            return false;
        }

        foreach ($this->getThirdPartyLoginAsSessionKeys() as $key) {
            if ($session->has($key)) {
                return true;
            }
        }

        return false;
    }

    public function destroy(SessionInterface $session): void
    {
        $session->remove($this->sessionKey);

        // make sure that the third-party modules did not left any data, which we will check
        foreach ($this->getThirdPartyLoginAsSessionKeys() as $key) {
            $session->remove($key);
        }
    }

    private function isRatepayAdminSession(SessionInterface $session): bool
    {
        return $session->get($this->sessionKey) === true;
    }

    private function getThirdPartyLoginAsSessionKeys(): array
    {
        $keys = [];

        // login as (module: https://store.shopware.com/de/jlau706451421896/als-kunde-einloggen.html)
        if (defined('Jlau\LoginAsCustomer\Controller\LoginAsCustomer::SESSION_NAME')) {
            $keys[] = constant('Jlau\LoginAsCustomer\Controller\LoginAsCustomer::SESSION_NAME');
        }

        // login as (module: https://store.shopware.com/de/swpa452746080451m/als-kunde-einloggen.html)
        if (defined('Swpa\SwpaLoginAsCustomer\Service\LoginService::ADMIN_CUSTOMER_CONTEXT_EXTENSION')) {
            $keys[] = constant('Swpa\SwpaLoginAsCustomer\Service\LoginService::ADMIN_CUSTOMER_CONTEXT_EXTENSION');
        }

        return $keys;
    }
}
