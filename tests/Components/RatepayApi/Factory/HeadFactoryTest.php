<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\RatepayApi\Factory;

use PHPUnit\Framework\TestCase;
use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\ProfileRequestData;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Factory\Mock;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;

class HeadFactoryTest extends TestCase
{
    use KernelTestBehaviour;

    public function testGetData(): void
    {
        $factory = Mock::createHeadFactory();

        $profileConfig = new ProfileConfigEntity();
        $profileConfig->setProfileId('test-123');
        $profileConfig->setSecurityCode('123-test');

        /** @var Head $head */
        $head = $factory->getData(new ProfileRequestData(Context::createDefaultContext(), $profileConfig));

        self::assertEquals('cli/cronjob/api', $head->getSystemId());
        self::assertEquals('Shopware', $head->getMeta()->getSystems()->getSystem()->getName());
        self::assertEquals('123/456', $head->getMeta()->getSystems()->getSystem()->getVersion());
        self::assertEquals('test-123', $head->getCredential()->getProfileId());
        self::assertEquals('123-test', $head->getCredential()->getSecuritycode());
    }

    public function testGetDataWithIP(): void
    {
        $factory = Mock::createHeadFactory();

        $_SERVER['SERVER_ADDR'] = '123.456.789.987';

        /** @var Head $head */
        $head = $factory->getData(new ProfileRequestData(Context::createDefaultContext(), new ProfileConfigEntity()));

        self::assertEquals('123.456.789.987', $head->getSystemId());
    }
}
