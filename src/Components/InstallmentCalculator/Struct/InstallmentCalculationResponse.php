<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Struct;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class InstallmentCalculationResponse extends StoreApiResponse
{
    /**
     * @var ArrayStruct
     */
    protected $object;

    /**
     * @param array{translations: array, plan: array, transactionId: string} $data
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
