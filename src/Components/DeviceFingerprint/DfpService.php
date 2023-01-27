<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\DeviceFingerprint;

use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\PluginConfig\Service\ConfigService;
use RatePAY\Service\DeviceFingerprint;
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

    private ConfigService $configService;
    private EntityRepository $orderCustomerRepository;

    public function __construct(
        ConfigService $configService,
        EntityRepository $orderCustomerRepository
    )
    {
        $this->configService = $configService;
        $this->orderCustomerRepository = $orderCustomerRepository;
    }

    /**
     * generates the dfp-id based on sales-channel-context or order-entity.
     * provide the user-agent via header or a request variable `userAgent` to generate a more unique device-identifier
     * the request-variable is prioritized
     * @inheritDoc
     */
    public function generatedDfpId(Request $request, $baseData): ?string
    {
        if (!$this->isDfpRequired($baseData)) {
            return null;
        }

        if ($baseData instanceof OrderEntity && $token = $this->getOrderDeviceToken($baseData)) {
            // token has been used for (failed) payment. So we can reuse it.
            return $token;
        }

        $userAgent = $request->get('userAgent') ?? null;
        $userAgent = $userAgent ?? $request->headers->get('User-Agent');

        $dataForId = [
            ((string)$userAgent)
        ];
        if ($baseData instanceof SalesChannelContext) {
            $dataForId[] = (string)$this->getCustomerFallBack($baseData);
        }
        $generatedId = md5(implode('', $dataForId));

        $prefix = $this->getDfpPrefix($baseData);

        // replace the beginning of the generated id with the generated prefix
        return $prefix . substr($generatedId, strlen($prefix));
    }

    /**
     * generates a unique prefix for the current cart.
     * we add this prefix to the beginning of the dfp-id to make sure we can validate it when using a headless application
     * if the client does not provide a User-Agent header, the client have to provide a dfp-id via request
     * @var SalesChannelContext|OrderEntity $object
     */
    private function getDfpPrefix($object): string
    {
        if ($object instanceof SalesChannelContext) {
            $identifier = md5($object->getToken());
        } else if ($object instanceof OrderEntity) {
            if ($token = $this->getOrderDeviceToken($object)) {
                // order payment has been failed, and we have to reuse the token.
                // the validation of the token will successfully, because in this special case the prefix got not
                // appended to the generated token, cause the generated token is just the same token as from the oder
                $identifier = $token;
            } else {
                $identifier = md5($object->getId());
            }
        } else {
            throw new \RuntimeException(sprintf('DFP: object of type %s was not expected', get_class($object)));
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
        if ($context->getCustomer() === null) {
            return null;
        }

        $orderCriteria = new Criteria();
        $orderCriteria->addFilter(new EqualsFilter('customerId', $context->getCustomerId()));
        $orderCriteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));
        $orderCriteria->setLimit(1);
        /** @var OrderCustomerEntity $orderCustomer */
        $orderCustomer = $this->orderCustomerRepository->search($orderCriteria, $context->getContext())->last();
        $date = $orderCustomer ? $orderCustomer->getCreatedAt() : null;
        $date = $date ?? $context->getCustomer()->getLastLogin() ?? $context->getCustomer()->getUpdatedAt();

        return $date ? (string)$date->getTimestamp() : null;
    }

    /**
     * returns the device token of the order, if the payment has been failed/not completed.
     */
    private function getOrderDeviceToken(OrderEntity $order): ?string
    {
        /** @var RatepayOrderDataEntity $ratepayExtension */
        $ratepayExtension = $order->getExtension(OrderExtension::EXTENSION_NAME);

        if ($ratepayExtension && !$ratepayExtension->isSuccessful()) {
            return $ratepayExtension->getAdditionalData('deviceIdentToken');
        }

        return null;
    }

    public function isDfpIdValid($baseData, string $dfpId = null): bool
    {
        $prefix = $this->getDfpPrefix($baseData);

        // verify if the prefix is at the beginning of the id
        return substr($dfpId, 0, strlen($prefix)) === $prefix;
    }

    public function getDfpSnippet(Request $request, $baseData): ?string
    {
        if ($id = $this->generatedDfpId($request, $baseData)) {
            $dfpHelper = new DeviceFingerprint($this->configService->getDeviceFingerprintSnippetId());
            return str_replace('\"', '"', $dfpHelper->getDeviceIdentSnippet($id));
        }

        return null;
    }

    public function isDfpRequired($object): bool
    {
        return true;
    }
}
