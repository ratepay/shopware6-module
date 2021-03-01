<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Util;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

class CriteriaHelper
{
    public static function getCriteriaForOrder($orderId)
    {
        $criteria = new Criteria([$orderId]);
        //$criteria->addAssociation(OrderExtension::RATEPAY_DATA);
        $criteria->addAssociation('currency');
        $criteria->addAssociation('language.locale');
        $criteria->addAssociation('addresses.country');
        $criteria->addAssociation('addresses.salutation');
        $criteria->addAssociation('orderCustomer.customer');
        //$criteria->addAssociation('lineItems.'.OrderLineItemExtension::RATEPAY_DATA);
        $criteria->addAssociation('lineItems.product');
        $criteria->addAssociation('deliveries.shippingMethod');
        $criteria->addAssociation('deliveries.positions');
        $criteria->addAssociation('deliveries.positions.orderLineItem');
        $criteria->addAssociation('deliveries.shippingOrderAddress');
        $criteria->addAssociation('deliveries.shippingOrderAddress.country');
        $criteria->addAssociation('deliveries.shippingOrderAddress.countryState');
        $criteria->addAssociation('deliveries.shippingOrderAddress.salutation');
        $criteria->addAssociation('transactions.paymentMethod');
        $criteria->addAssociation('documents.documentType');
        $criteria->addSorting(new FieldSorting('lineItems.createdAt'));

        $criteria->getAssociation('transactions')->addSorting(new FieldSorting('createdAt'));

        return $criteria;
    }

    public static function getCriteriaForProfileConfig($profileConfigIds)
    {
        $criteria = new Criteria($profileConfigIds);
        $criteria->addAssociation(ProfileConfigEntity::FIELD_SALES_CHANNEL);
        $criteria->addAssociation(ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_INSTALLMENT_CONFIG);
        $criteria->addAssociation(ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_PAYMENT_METHOD);

        return $criteria;
    }
}
