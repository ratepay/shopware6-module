# Ratepay GmbH - Shopware6 Payment Module
============================================

|Module | Ratepay Payment Plugin for Shopware 6
|------|----------
|Author | Interlutions GmbH
|Shop Version | `6.3.5.0` - `6.3.x.x`
|Link | http://www.ratepay.com
|Mail | integration@ratepay.com
|Full Documentation | https://ratepay.gitbook.io/shopware6/
|Legal Disclaimer|https://ratepay.gitbook.io/docs/#legal-disclaimer

## Installation via Shopware Store
1. Buy the plugin for free within the Shopware Store
2. Visit your Shopware Administration
3. Click Settings > System > Plugins -> Licenses and download the Ratepay Module
4. Click Settings > System > Plugins the Ratepay Payment Extension is now listed in My Plugins
5. Choose Installation and after this activate the module

## Installation via ZIP-File
1. Download the latest release from our Github page
2. Visit your Shopware Administration
3. Click Settings > System > Plugins
4. Click Upload Plugin and chose the previously downloaded ZIP file
5. Choose Installation and after this activate the Ratepay module

## Installation via composer
1. execute the following command in your main shopware directory: `composer require ratepay/shopware6-module`
2. Click Settings > System > Plugins the Ratepay Payment Extension is now listed in My Plugins
3. Choose Installation and after this activate the module

## Changelog

### Version 1.2.0 - WIP
* RATESWSX-115 - apply Shopware fix + increase Shopware min. compatibility
* RATESWSX-162: add support for multiple tracking ids
* RATESWSX-169 - add account holder select for B2B accounts

### Version 1.1.0 - Released on 2021-02-15
* RATESWSX-149 - change translation of "date of birth"
* RATESWSX-150 - introduce payment query into payment flow
* RATESWSX-151 - fixed missing dependency during `administration:build` (`node_modules`)
* RATESWSX-157 - api-logs: add search function
* RATESWSX-158 - credit/debit: set custom tax rate
* RATESWSX-160 - add street-additional PaymentRequest operation
* RATESWSX-161 - add missing oder-id to operation requests
* RATESWSX-164 - add customer number to PaymentRequest operation

### Version 1.0.0
- initial release
