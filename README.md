# Ratepay GmbH - Shopware 6 Payment Module

| Module             | Ratepay Payment Plugin for Shopware 6                                                                                         |
|--------------------|-------------------------------------------------------------------------------------------------------------------------------|
| Author             | Fatchip GmbH                                                                                                             |
| Shop Version       | `6.3.0.2 to 6.7.x.x`                                                                                                          |
| Link               | http://www.ratepay.com                                                                                                        |
| Mail               | integration@ratepay.com                                                                                                       |
| Full Documentation | [click here](https://docs.ratepay.com/docs/developer/shop_modules/shopware/shopware_6/ratepay_payment_plugin_for_shopware_6/) |
| Legal Disclaimer   | [click here](https://docs.ratepay.com/docs/developer/shop_modules/shopware/overview)                                                  |

## Installation via Shopware Store

1. Buy the plugin for free within the [Shopware Store](https://store.shopware.com/en/rpay809217953125f/ratepay-payment-plugin-for-shopware-6.html)
2. Visit your Shopware Administration
3. Click Extensions > My extensions > Apps and download the Ratepay Module
4. Within Extensions > My extensions > Apps, the Ratepay Payment Extension should be now listed within your Apps
5. Choose Installation and after this activate the module

## Installation via ZIP-File

1. Download the latest release from our GitHub page
2. Visit your Shopware Administration
3. Click Extensions > My extensions > Apps
4. Click Upload extension and chose the previously downloaded ZIP file
5. Choose Installation and after this activate the Ratepay module

## Installation via composer

1. Execute the following command in your main shopware directory: `composer require ratepay/shopware6-module`
2. Within Extensions > My extensions > Apps, the Ratepay Payment Extension should be now listed within your Apps
3. Choose Installation and after this activate the module

## Feature flags

You can modify the module's behavior and activate or deactivate features by toggling feature flags.

Enable the flags in the module configuration.

Please utilize the feature flags judiciously, only if you are well-versed in their operation.

Here is a list with the feature flags:

| Flag-ID     | Description                                                                              |
|-------------|------------------------------------------------------------------------------------------|
| FF-BLOCK-PR | if enabled, no payment requests got executed. instead a payment exception got simulated. |
