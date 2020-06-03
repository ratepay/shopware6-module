<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler;

use phpDocumentor\Reflection\Types\Integer;
use Shopware\Core\Framework\Validation\Constraint\ArrayOfUuid;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\Type;

class DebitPaymentHandler extends AbstractPaymentHandler
{
    const RATEPAY_METHOD = 'ELV';

    public function getValidationDefinitions(): array
    {
        return [
            'iban' => [new NotBlank(), new Iban()]
        ];
    }
}
