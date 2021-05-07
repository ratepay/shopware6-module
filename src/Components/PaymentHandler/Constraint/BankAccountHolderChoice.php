<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler\Constraint;

use Ratepay\RpayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\ChoiceValidator;

class BankAccountHolderChoice extends Choice
{
    public $message = AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . 'RP_INVALID_BANK_ACCOUNT_HOLDER';

    public $multipleMessage = AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . 'RP_INVALID_BANK_ACCOUNT_HOLDER';

    public $minMessage = AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . 'RP_INVALID_BANK_ACCOUNT_HOLDER';

    public $maxMessage = AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . 'RP_INVALID_BANK_ACCOUNT_HOLDER';

    protected static $errorNames = [
        self::NO_SUCH_CHOICE_ERROR => 'RP_INVALID_BANK_ACCOUNT_HOLDER',
        self::TOO_FEW_ERROR => 'RP_INVALID_BANK_ACCOUNT_HOLDER',
        self::TOO_MANY_ERROR => 'RP_INVALID_BANK_ACCOUNT_HOLDER',
    ];

    public function __construct(array $choices)
    {
        parent::__construct([
            'min' => 1,
            'max' => 1,
            'choices' => $choices,
        ]);
    }

    public function validatedBy(): string
    {
        return ChoiceValidator::class;
    }
}
