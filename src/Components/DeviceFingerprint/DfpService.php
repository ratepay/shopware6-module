<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\DeviceFingerprint;

use Ratepay\RpayPayments\Components\DeviceFingerprint\Struct\CartDataStruct;
use Ratepay\RpayPayments\Core\Entity\Extension\OrderExtension;
use Ratepay\RpayPayments\Core\Entity\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Core\PluginConfigService;
use RatePAY\Service\DeviceFingerprint;
use Shopware\Core\Checkout\Cart\AbstractCartPersister;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class DfpService implements DfpServiceInterface
{
    private const CART_DATA_KEY = 'ratepayDeviceIdentToken';

    public function __construct(
        private readonly PluginConfigService $configService,
        private readonly CartService $cartService,
        private readonly AbstractCartPersister $cartPersister
    ) {
    }

    /**
     * generates the dfp-id based on sales-channel-context or order-entity.
     * provide the user-agent via header or a request variable `userAgent` to generate a more unique device-identifier
     * the request-variable is prioritized
     */
    public function generatedDfpId(Request $request, SalesChannelContext $salesChannelContext, OrderEntity $orderEntity = null): ?string
    {
        if (!$this->isDfpRequired($salesChannelContext, $orderEntity)) {
            return null;
        }

        if ($orderEntity instanceof OrderEntity) {
            return $this->getOrderDeviceToken($orderEntity);
        }

        return $this->getCustomerToken($request, $salesChannelContext);
    }

    public function getDfpSnippet(Request $request, SalesChannelContext $salesChannelContext, OrderEntity $orderEntity = null): ?string
    {
        if ($id = $this->generatedDfpId($request, $salesChannelContext, $orderEntity)) {
            $dfpHelper = new DeviceFingerprint($this->configService->getDeviceFingerprintSnippetId());
            return str_replace('\"', '"', $dfpHelper->getDeviceIdentSnippet($id));
        }

        return null;
    }

    public function isDfpRequired(SalesChannelContext $salesChannelContext, OrderEntity $orderEntity = null): bool
    {
        return true;
    }

    private function getOrderDeviceToken(OrderEntity $order): string
    {
        /** @var RatepayOrderDataEntity|null $ratepayExtension */
        $ratepayExtension = $order->getExtension(OrderExtension::EXTENSION_NAME);

        $token = null;
        if ($ratepayExtension && !$ratepayExtension->isSuccessful()) {
            $token = $ratepayExtension->getAdditionalData('deviceIdentToken');
        }

        return is_string($token) ? $token : md5($order->getId());
    }

    private function getCustomerToken(Request $request, SalesChannelContext $context): string
    {
        $cart = $this->cartService->getCart($context->getToken(), $context);

        $existingHashData = $cart->getExtension(self::CART_DATA_KEY);
        if (!$existingHashData instanceof CartDataStruct) {
            $existingHashData = new CartDataStruct(Uuid::randomHex());
        }

        $hash = md5(implode('', [
            'uuid' => $existingHashData->uuid,
            'token' => $context->getToken(),

            // user-agent is only required for logged-in users. This will prevent that they can not switch the device
            'user-agent' => $request->get('userAgent') ?? $request->headers->get('User-Agent') ?? $context->getCustomer()?->getRemoteAddress(),

            // if user-agent is not given, we need another parameter. We will try the last-login.
            'last-login' => $context->getCustomer()?->getLastLogin()?->getTimestamp(),
        ]));

        if ($hash !== $existingHashData->hash) {
            $existingHashData->hash = $hash;
            $cart->addExtension(self::CART_DATA_KEY, $existingHashData);
            $this->cartPersister->save($cart, $context);
        }

        return $hash;
    }
}
