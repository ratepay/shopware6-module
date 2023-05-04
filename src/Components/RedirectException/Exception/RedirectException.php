<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RedirectException\Exception;

use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectException extends Exception
{
    public function __construct(
        private readonly RedirectResponse $redirectResponse,
        string $message = '',
        int $code = 0,
        Exception $previousException = null
    ) {
        parent::__construct($message, $code, $previousException);
    }

    public function getRedirectResponse(): RedirectResponse
    {
        return $this->redirectResponse;
    }
}
