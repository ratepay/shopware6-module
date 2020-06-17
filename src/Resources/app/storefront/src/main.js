/*
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Import all necessary Storefront plugins and scss files
import RatepayInstallment from './RatepayCheckout/Installment';

// Register them via the existing PluginManager
const PluginManager = window.PluginManager;
PluginManager.register('ratepay-installment', RatepayInstallment, '[data-ratepay-installment="true"]');
