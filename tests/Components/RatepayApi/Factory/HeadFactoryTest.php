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
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;

class HeadFactoryTest extends TestCase
{
    use KernelTestBehaviour;

    public function testGetData(): void
    {
        $headFactory = new HeadFactory(new EventDispatcher(), new RequestStack(), '123', '456');

        /** @var Head $data */
        $data = $headFactory->getData($this->createMock(PaymentRequestData::class));

        self::assertEquals('cli/cronjob/api', $data->getSystemId());
        self::assertEquals('Shopware', $data->getMeta()->getSystems()->getSystem()->getName());
        self::assertEquals('123/456', $data->getMeta()->getSystems()->getSystem()->getVersion());
    }

    public function testGetDataWithIP(): void
    {
        $headFactory = new HeadFactory(new EventDispatcher(), new RequestStack(), '', '');

        $_SERVER['SERVER_ADDR'] = '123.456.789.987';

        /** @var Head $data */
        $data = $headFactory->getData($this->createMock(PaymentRequestData::class));

        self::assertEquals('123.456.789.987', $data->getSystemId());
    }

}
