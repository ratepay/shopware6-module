<?php declare(strict_types=1);

namespace Ratepay\RpayPayments\Components\DeviceFingerprint\Constraint;

use Ratepay\RpayPayments\Components\DeviceFingerprint\DfpServiceInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Validator\Constraint;

class DfpConstraint extends Constraint
{

    public const ERROR_CODE = 'd8afee2c-7ad7-44e2-aec8-3d8d6a2eadb9';

    protected static $errorNames = [
        self::ERROR_CODE => 'RP_INVALID_DFP',
    ];

    private DfpServiceInterface $dfpService;

    /**
     * @var OrderEntity|SalesChannelContext
     */
    private $object;

    /**
     * @param DfpServiceInterface $dfpService
     * @param SalesChannelContext|OrderEntity $object
     */
    public function __construct(DfpServiceInterface $dfpService, $object)
    {
        parent::__construct();
        $this->dfpService = $dfpService;
        $this->object = $object;
    }

    public function getDfpService(): DfpServiceInterface
    {
        return $this->dfpService;
    }

    /**
     * @return OrderEntity|SalesChannelContext
     */
    public function getObject()
    {
        return $this->object;
    }

}
