<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto;

use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PaymentInitData extends AbstractRequestData
{
    /**
     * @var SalesChannelContext
     */
    private $salesChannelContext;

    public function __construct(SalesChannelContext $salesChannelContext)
    {
        parent::__construct($salesChannelContext->getContext());
        $this->salesChannelContext = $salesChannelContext;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }
}
