<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\RatepayApi\Service\Request;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\ProfileRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\AbstractRequest;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Service\Request\ProfileRequestServiceMock;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;

class ProfileRequestServiceTest extends AbstractRequestService
{
    use KernelTestBehaviour;

    public function testGetRequestHead(): void
    {
        $service = $this->getServiceMock();

        $profileConfig = new ProfileConfigEntity();
        $profileConfig->setProfileId('test-123');
        $profileConfig->setSecurityCode('123-test');

        $head = $service->getRequestHead(new ProfileRequestData(Context::createDefaultContext(), $profileConfig));
        self::assertEquals('test-123', $head->getCredential()->getProfileId());
        self::assertEquals('123-test', $head->getCredential()->getSecuritycode());
    }

    public function testGetRequestContent(): void
    {
        $service = $this->getServiceMock();

        $content = $service->getRequestContent(new ProfileRequestData(Context::createDefaultContext(), new ProfileConfigEntity()));
        self::assertNull($content, 'should be null, cause the request does not require a content.');
    }

    protected function getServiceMock(): ?AbstractRequest
    {
        return new ProfileRequestServiceMock();
    }
}
