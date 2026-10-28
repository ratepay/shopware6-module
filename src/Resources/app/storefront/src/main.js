/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import RatepayInstallment from './RatepayCheckout/Installment';
import RatepayInstallmentPaymentSwitch from './RatepayCheckout/InstallmentPaymentSwitch';

const PluginManager = window.PluginManager;
let pluginList = PluginManager.getPluginList();

if(!('RatepayInstallment' in pluginList)) {
    PluginManager.register('RatepayInstallment', RatepayInstallment, '[data-ratepay-installment="true"]');
}

if(!('RatepayInstallmentPaymentSwitch' in pluginList)) {
    PluginManager.register('RatepayInstallmentPaymentSwitch', RatepayInstallmentPaymentSwitch, '[data-ratepay-installment-payment-switch="true"]');
}
