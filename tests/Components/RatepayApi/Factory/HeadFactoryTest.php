<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\RatepayApi\Factory;

use PHPUnit\Framework\TestCase;
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto\PaymentQueryData;
use Ratepay\RpayPayments\Components\ProfileConfig\Dto\ProfileRequestData;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AddCreditData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentInitData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Tests\Mock\Model\OrderMock;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcher;

class HeadFactoryTest extends TestCase
{
    use KernelTestBehaviour;

    public function testGetData(): void
    {
        $factory = $this->getFactory();
        $profileConfig = $this->getProfileConfig();

        $head = $factory->getData(new ProfileRequestData(Context::createDefaultContext(), $profileConfig));

        self::assertEquals('cli/cronjob/api', $head->getSystemId());
        self::assertEquals('Shopware', $head->getMeta()->getSystems()->getSystem()->getName());
        self::assertEquals('shopware-version_plugin-version', $head->getMeta()->getSystems()->getSystem()->getVersion());
        self::assertEquals('profile-id', $head->getCredential()->getProfileId());
        self::assertEquals('security-code', $head->getCredential()->getSecuritycode());
    }

    public function testGetDataWithIP(): void
    {
        $factory = $this->getFactory();
        $profileConfig = $this->getProfileConfig();

        $_SERVER['SERVER_ADDR'] = '123.456.789.987';

        $head = $factory->getData(new ProfileRequestData(Context::createDefaultContext(), $profileConfig));

        self::assertEquals('123.456.789.987', $head->getSystemId());
    }

    public function testTransactionId()
    {
        $factory = $this->getFactory();
        $profileConfig = $this->getProfileConfig();

        $orderEntity = OrderMock::createMock();

        $requestDataObjects = [
            [true, new OrderOperationData(Context::createDefaultContext(), $orderEntity, OrderOperationData::OPERATION_DELIVER)],
            [true, new PaymentRequestData($this->createMock(SalesChannelContext::class), $orderEntity, $orderEntity->getTransactions()->first(), new RequestDataBag(), 'transaction-id')],
            [true, new AddCreditData(Context::createDefaultContext(), $orderEntity, [])],
            [false, new PaymentQueryData($this->createMock(SalesChannelContext::class), $this->createMock(Cart::class), new RequestDataBag(), 'transaction-id')],
            [false, new PaymentInitData($profileConfig, Context::createDefaultContext())],
        ];

        foreach ($requestDataObjects as [$needTransactionId, $requestData]) {
            $requestData->setProfileConfig($profileConfig);

            $head = $factory->getData($requestData);
            if ($needTransactionId) {
                self::assertNotNull($head->getTransactionId(), sprintf('if object `%s` is used as request data for the head-factory, the factory must set a transaction-id', get_class($requestData)));
                self::assertEquals('transaction-id', $head->getTransactionId());
            } else {
                self::assertNull($head->getTransactionId(), sprintf('if object `%s` is used as request data for the head-factory, the factory should NOT set a transaction-id', get_class($requestData)));
            }
        }
    }

    private function getFactory(): HeadFactory
    {
        return new HeadFactory(new EventDispatcher(), 'shopware-version', 'plugin-version');
    }

    private function getProfileConfig(): ProfileConfigEntity
    {
        $profileConfig = new ProfileConfigEntity();
        $profileConfig->__set(ProfileConfigEntity::FIELD_PROFILE_ID, 'test-123');
        $profileConfig->__set(ProfileConfigEntity::FIELD_SECURITY_CODE, '123-test');
        return $profileConfig;
    }
}
