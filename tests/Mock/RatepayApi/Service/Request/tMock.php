<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Mock\RatepayApi\Service\Request;

use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;

trait tMock
{
    public function getRequestHead(AbstractRequestData $requestData): Head
    {
        return parent::getRequestHead($requestData);
    }

    public function getRequestContent(AbstractRequestData $requestData): ?Content
    {
        return parent::getRequestContent($requestData);
    }

    public function getProfileConfig(AbstractRequestData $requestData): ProfileConfigEntity
    {
        return parent::getProfileConfig($requestData);
    }
}
