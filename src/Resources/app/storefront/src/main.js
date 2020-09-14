/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Import all necessary Storefront plugins and scss files
import RatepayInstallment from './RatepayCheckout/Installment';
import RatepayInstallmentPaymentSwitch from './RatepayCheckout/InstallmentPaymentSwitch';

// Register them via the existing PluginManager
const PluginManager = window.PluginManager;

PluginManager.register('ratepay-installment', RatepayInstallment, '[data-ratepay-installment="true"]');
PluginManager.register(
    'ratepay-installment-payment-switch',
    RatepayInstallmentPaymentSwitch,
    '[data-ratepay-installment-payment-switch="true"]'
);
