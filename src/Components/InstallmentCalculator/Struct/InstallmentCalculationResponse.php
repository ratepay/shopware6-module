<?php

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Struct;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class InstallmentCalculationResponse extends StoreApiResponse
{

    /** @var ArrayStruct */
    protected $object;

    /**
     * @param $data array{translations: array, plan: array, transactionId: string}
     */
    public function __construct(array $data)
    {
        parent::__construct(new ArrayStruct($data));
    }

    public function getTranslations(): array
    {
        return $this->object->get('translations');
    }

    public function getPlan(): array
    {
        return $this->object->get('plan');
    }

    public function getTransactionId(): string
    {
        return $this->object->get('transactionId');
    }
}
