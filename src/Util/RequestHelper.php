<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Util;

use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

class RequestHelper
{
    public static function getArrayBag(Request $request, string $key, ?array $default = null): ?RequestDataBag
    {
        $data = self::getArray($request, $key, $default);

        return $data ? new RequestDataBag($data) : null;
    }

    public static function getArray(Request $request, string $key, ?array $default = null): ?array
    {
        $allData = $request->request->all();

        $data = $allData[$key] ?? $default;

        if ($data !== null && !is_array($data)) {
            throw new BadRequestException(sprintf('parameter %s has to by a array. %s given', $key, get_debug_type($data)));
        }

        return $data;
    }
}
