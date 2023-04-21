<?php

declare(strict_types=1);

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
    /**
     * @var array<string, string>
     */
    protected const ERROR_NAMES = [
        self::NO_SUCH_CHOICE_ERROR => 'RP_INVALID_BANK_ACCOUNT_HOLDER',
        self::TOO_FEW_ERROR => 'RP_INVALID_BANK_ACCOUNT_HOLDER',
        self::TOO_MANY_ERROR => 'RP_INVALID_BANK_ACCOUNT_HOLDER',
    ];

    /**
     * @var string
     */
    public $message = AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . 'RP_INVALID_BANK_ACCOUNT_HOLDER';

    /**
     * @var string
     */
    public $multipleMessage = AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . 'RP_INVALID_BANK_ACCOUNT_HOLDER';

    /**
     * @var string
     */
    public $minMessage = AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . 'RP_INVALID_BANK_ACCOUNT_HOLDER';

    /**
     * @var string
     */
    public $maxMessage = AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . 'RP_INVALID_BANK_ACCOUNT_HOLDER';

    /**
     * @var string[]
     * @deprecated since Symfony 6.1, use const ERROR_NAMES instead
     */
    protected static $errorNames = self::ERROR_NAMES;

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
