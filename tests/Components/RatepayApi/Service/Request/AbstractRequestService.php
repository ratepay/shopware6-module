<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\RatepayApi\Service\Request;

use PHPUnit\Framework\TestCase;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\AbstractRequest;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;

abstract class AbstractRequestService extends TestCase
{
    use KernelTestBehaviour;

    private static $eventNames = [
        'EVENT_BUILD_HEAD',
        'EVENT_BUILD_CONTENT',
        'EVENT_SUCCESSFUL',
        'EVENT_FAILED',
    ];

    public function testRequiredConstants(): void
    {
        $mock = $this->getServiceMock();
        $requestClassName = get_parent_class($mock);

        foreach (self::$eventNames as $name) {
            self::assertTrue(defined($requestClassName . '::' . $name), 'the class ' . $requestClassName . ' must have implemented the constant `' . $name . '`');
        }
    }

    abstract protected function getServiceMock(): ?AbstractRequest;
}
