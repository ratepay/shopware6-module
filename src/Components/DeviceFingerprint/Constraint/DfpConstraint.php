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
use RuntimeException;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Validator\Constraint;

class DfpConstraint extends Constraint
{
    /**
     * @var string
     */
    public const ERROR_CODE = 'd8afee2c-7ad7-44e2-aec8-3d8d6a2eadb9';

    /**
     * @var string[]
     */
    protected static $errorNames = [
        self::ERROR_CODE => 'RP_INVALID_DFP',
    ];

    private DfpServiceInterface $dfpService;

    /**
     * @var OrderEntity|SalesChannelContext
     * @noinspection PhpDocFieldTypeMismatchInspection
     */
    private object $object;

    /**
     * @param SalesChannelContext|OrderEntity $object
     * @noinspection PhpDocSignatureInspection
     */
    public function __construct(DfpServiceInterface $dfpService, object $object)
    {
        parent::__construct();
        $this->dfpService = $dfpService;

        /** @phpstan-ignore-next-line */
        if (!$object instanceof SalesChannelContext && !$object instanceof OrderEntity) {
            throw new RuntimeException('$object should be on of OrderEntity or SalesChannelContext');
        }

        $this->object = $object;
    }

    public function getDfpService(): DfpServiceInterface
    {
        return $this->dfpService;
    }

    /**
     * @return OrderEntity|SalesChannelContext
     */
    public function getObject(): object
    {
        return $this->object;
    }
}
