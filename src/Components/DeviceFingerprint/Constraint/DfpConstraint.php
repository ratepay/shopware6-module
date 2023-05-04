<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\DeviceFingerprint\Constraint;

use Ratepay\RpayPayments\Components\DeviceFingerprint\DfpServiceInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Validator\Constraint;

class DfpConstraint extends Constraint
{
    /**
     * @var string
     */
    final public const ERROR_CODE = 'd8afee2c-7ad7-44e2-aec8-3d8d6a2eadb9';

    /**
     * @var string[]
     */
    protected static $errorNames = [
        self::ERROR_CODE => 'RP_INVALID_DFP',
    ];

    public function __construct(
        private readonly DfpServiceInterface $dfpService,
        private readonly OrderEntity|SalesChannelContext $object
    ) {
        parent::__construct();
    }

    public function getDfpService(): DfpServiceInterface
    {
        return $this->dfpService;
    }

    public function getObject(): OrderEntity|SalesChannelContext
    {
        return $this->object;
    }
}
