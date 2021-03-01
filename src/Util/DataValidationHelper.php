<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Util;

use Shopware\Core\Framework\Validation\DataValidationDefinition;

class DataValidationHelper
{
    public static function addSubConstraints(DataValidationDefinition $parent, array $children): DataValidationDefinition
    {
        foreach ($children as $key => $constraints) {
            if ($constraints instanceof DataValidationDefinition) {
                $parent->addSub($key, $constraints);
            } else {
                call_user_func_array([$parent, 'add'], array_merge([$key], $constraints));
            }
        }

        return $parent;
    }
}
