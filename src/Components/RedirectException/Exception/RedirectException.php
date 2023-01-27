<?php

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
    private RedirectResponse $redirectResponse;

    public function __construct(
        RedirectResponse $redirectResponse,
        $message = '',
        $code = 0,
        Exception $previousException = null
    ) {
        $this->redirectResponse = $redirectResponse;
        parent::__construct($message, $code, $previousException);
    }

    public function getRedirectResponse(): RedirectResponse
    {
        return $this->redirectResponse;
    }
}
