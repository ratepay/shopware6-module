<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Util;

use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class RequestHelper
{
    final public const WRAPPER_KEY = 'paymentDetails';

    final public const RATEPAY_DATA_KEY = 'ratepay';

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

    public static function isRatepayDataWrapped(ParameterBag $parameterBag): bool
    {
        return $parameterBag->has(self::WRAPPER_KEY)
            && ($pd = $parameterBag->get(self::WRAPPER_KEY)) instanceof ParameterBag
            && $pd->has(self::RATEPAY_DATA_KEY);
    }

    public static function getRatepayData(ParameterBag $parameterBag): ?DataBag
    {
        if (self::isRatepayDataWrapped($parameterBag)) {
            $wrapper = $parameterBag->get(self::WRAPPER_KEY);
            if ($wrapper instanceof ParameterBag) {
                $ratepayData = $wrapper->get(self::RATEPAY_DATA_KEY);
            } elseif (is_array($wrapper)) {
                $ratepayData = $wrapper[self::RATEPAY_DATA_KEY] ?? null;
            }
        } else {
            $ratepayData = $parameterBag->get(self::RATEPAY_DATA_KEY);
        }

        if (!isset($ratepayData)) {
            return null;
        }

        if ($ratepayData instanceof DataBag) {
            return $ratepayData;
        }

        return new DataBag(is_array($ratepayData) ? $ratepayData : $ratepayData->all());
    }
}
