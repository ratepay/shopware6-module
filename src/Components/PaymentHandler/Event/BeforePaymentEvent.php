<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler\Event;

use Ratepay\RatepayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\ShopwareEvent;
use Symfony\Contracts\EventDispatcher\Event;

class BeforePaymentEvent extends Event implements ShopwareEvent
{

    /**
     * @var PaymentRequestData
     */
    private $paymentRequestData;
    /**
     * @var Context
     */
    private $context;

    public function __construct(PaymentRequestData $paymentRequestData, Context $context)
    {
        $this->paymentRequestData = $paymentRequestData;
        $this->context = $context;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @return PaymentRequestData
     */
    public function getPaymentRequestData(): PaymentRequestData
    {
        return $this->paymentRequestData;
    }
}
