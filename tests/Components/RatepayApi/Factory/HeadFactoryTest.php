<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use PHPUnit\Framework\TestCase;
use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;

class HeadFactoryTest extends TestCase
{
    use KernelTestBehaviour;

    public function testGetData(): void
    {
        $headFactory = $this->getContainer()->get(HeadFactory::class);

        /** @var Head $data */
        $data = $headFactory->getData($this->createMock(PaymentRequestData::class));

        self::assertEquals('cli/cronjob/api', $data->getSystemId());
        self::assertEquals('Shopware', $data->getMeta()->getSystems()->getSystem()->getName());
        $shopwareVersion = $this->getContainer()->getParameter('kernel.shopware_version');
        $ratepayVersion = $this->getContainer()->getParameter('ratepay.shopware_payment.plugin_version');
        self::assertEquals($shopwareVersion . '/' . $ratepayVersion, $data->getMeta()->getSystems()->getSystem()->getVersion());
    }

    public function testGetDataWithIP(): void
    {
        $headFactory = $this->getContainer()->get(HeadFactory::class);

        $_SERVER['SERVER_ADDR'] = '123.456.789';

        /** @var Head $data */
        $data = $headFactory->getData($this->createMock(PaymentRequestData::class));

        self::assertEquals('123.456.789', $data->getSystemId());
    }

}
