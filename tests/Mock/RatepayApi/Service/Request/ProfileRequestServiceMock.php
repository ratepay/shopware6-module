<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Mock\RatepayApi\Service\Request;

use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\ProfileRequestService;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Factory\Mock;
use Symfony\Component\EventDispatcher\EventDispatcher;

/** @noinspection PhpSuperClassIncompatibleWithInterfaceInspection */
class ProfileRequestServiceMock extends ProfileRequestService
{
    use tMock;

    public function __construct()
    {
        parent::__construct(new EventDispatcher(), Mock::createHeadFactory());
    }
}
