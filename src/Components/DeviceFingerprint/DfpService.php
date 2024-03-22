<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\DeviceFingerprint;

use DateTimeInterface;
use Ratepay\RpayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RpayPayments\Core\Entity\Extension\OrderExtension;
use Ratepay\RpayPayments\Core\Entity\RatepayOrderDataEntity;
use RatePAY\Service\DeviceFingerprint;
use RuntimeException;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class DfpService implements DfpServiceInterface
{
    public function __construct(
        private readonly ConfigService $configService,
        private readonly EntityRepository $orderCustomerRepository
    ) {
    }

    /**
     * generates the dfp-id based on sales-channel-context or order-entity.
     * provide the user-agent via header or a request variable `userAgent` to generate a more unique device-identifier
     * the request-variable is prioritized
     */
    public function generatedDfpId(Request $request, OrderEntity|SalesChannelContext $baseData): ?string
    {
        if (!$this->isDfpRequired($baseData)) {
            return null;
        }

        if ($baseData instanceof OrderEntity && $token = $this->getOrderDeviceToken($baseData)) {
            // token has been used for (failed) payment. So we can reuse it.
            return $token;
        }

        $userAgent = $request->get('userAgent') ?? null;
        $userAgent ??= $request->headers->get('User-Agent');

        $dataForId = [
            ((string) $userAgent),
        ];
        if ($baseData instanceof SalesChannelContext) {
            $dataForId[] = (string) $this->getCustomerFallBack($baseData);
        }

        $generatedId = md5(implode('', $dataForId));

        $prefix = $this->getDfpPrefix($baseData);

        // replace the beginning of the generated id with the generated prefix
        return $prefix . substr($generatedId, strlen($prefix));
    }

    public function isDfpIdValid(OrderEntity|SalesChannelContext $baseData, string $dfpId = null): bool
    {
        $prefix = $this->getDfpPrefix($baseData);

        // verify if the prefix is at the beginning of the id
        return str_starts_with((string) $dfpId, $prefix);
    }

    public function getDfpSnippet(Request $request, OrderEntity|SalesChannelContext $baseData): ?string
    {
        if ($id = $this->generatedDfpId($request, $baseData)) {
            $dfpHelper = new DeviceFingerprint($this->configService->getDeviceFingerprintSnippetId());
            return str_replace('\"', '"', $dfpHelper->getDeviceIdentSnippet($id));
        }

        return null;
    }

    public function isDfpRequired(OrderEntity|SalesChannelContext $object): bool
    {
        return true;
    }

    /**
     * generates a unique prefix for the current cart.
     * we add this prefix to the beginning of the dfp-id to make sure we can validate it when using a headless application
     * if the client does not provide a User-Agent header, the client have to provide a dfp-id via request
     */
    private function getDfpPrefix(SalesChannelContext|OrderEntity $object): string
    {
        if ($object instanceof SalesChannelContext) {
            $identifier = md5((string) $object->getToken());
        } elseif ($object instanceof OrderEntity) {
            // order payment has been failed, and we have to reuse the token.
            // the validation of the token will successfully, because in this special case the prefix got not
            // appended to the generated token, cause the generated token is just the same token as from the oder
            $identifier = $this->getOrderDeviceToken($object) ?? md5((string) $object->getId());
        } else {
            throw new RuntimeException(sprintf('DFP: object of type %s was not expected', $object::class));
        }

        return substr($identifier, 0, 5);
    }

    /**
     * returns additional data for logged-in users and new orders.
     * we need this because the ID has to be reused when the customer is switch the payment/profile/...
     * because the cart-token is not unique for logged-in users, the last order-date got added.
     * if you order does exist, the last-login got added, because it could that the customer switched the device since last login.
     */
    private function getCustomerFallBack(SalesChannelContext $context): ?string
    {
        if (!$context->getCustomer() instanceof CustomerEntity) {
            return null;
        }

        $orderCriteria = new Criteria();
        $orderCriteria->addFilter(new EqualsFilter('customerId', $context->getCustomerId()));
        $orderCriteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));
        $orderCriteria->setLimit(1);
        /** @var OrderCustomerEntity|null $orderCustomer */
        $orderCustomer = $this->orderCustomerRepository->search($orderCriteria, $context->getContext())->last();
        $date = $orderCustomer instanceof OrderCustomerEntity ? $orderCustomer->getCreatedAt() : null;
        $date ??= $context->getCustomer()->getLastLogin() ?? $context->getCustomer()->getUpdatedAt();

        return $date instanceof DateTimeInterface ? (string) $date->getTimestamp() : null;
    }

    /**
     * returns the device token of the order, if the payment has been failed/not completed.
     */
    private function getOrderDeviceToken(OrderEntity $order): ?string
    {
        /** @var RatepayOrderDataEntity|null $ratepayExtension */
        $ratepayExtension = $order->getExtension(OrderExtension::EXTENSION_NAME);

        if ($ratepayExtension && !$ratepayExtension->isSuccessful()) {
            return $ratepayExtension->getAdditionalData('deviceIdentToken');
        }

        return null;
    }
}
