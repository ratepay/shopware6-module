# Changelog

## WIP

* FC#0132520 - add config options to change capture trigger, disable automatic refunds

## Version 8.0.0 - Released on 2025-09-18

* RATESWSX-332: add Shopware 6.7.x.x compatibility
* RATESWSX-332: build tool: migration from Webpack to Vite
* RATESWSX-332: upgrade code to be compatible with PHP 8.2, PHP 8.3 and PHP 8.4
* RATESWSX-332: upgrade composer version to be compatible with PHP 8.4

## Version 7.2.0 - Released on 2024-12-09

* RATESWSX-320: dfp: remove validation & improve user-identification
* RATESWSX-321: fix deletion of ratepay-data if payment-method got switched

## Version 7.1.2 - Released on 2024-10-17

* RATESWSX-318: fix reloading profile-config if legacy payment-methods are available

## Version 7.1.0 - Released on 2024-09-19

* RATESWSX-306: installment: prevent fatal error message in checkout on unreachable gateway
* RATESWSX-307: add support for login as (third-party modules and Shopware standard since 6.6.5.x)
* RATESWSX-308: remove decorated account order controller
* RATESWSX-309: improve payment filter / fix bug if no payment methods are available 
* RATESWSX-310: prevent bidirectionality if manual deliver/return/cancel has been processed

## Version 7.0.1 - Released on 2024-06-07

* RATESWSX-303: fix admin-session logout endless redirect & make admin-session urls more unified

## Version 7.0.0 - Released on 2024-04-16

* RATESWSX-271: update payment status on order-item operation
* RATESWSX-271: remove bidirectional order status configuration
* RATESWSX-294: add technical-name for payment methods
* RATESWSX-294: add support headless storefronts
* RATESWSX-294: Shopware 6.6.x.x compatibility
+ RATESWSX-296: fix compatibility with third-party PSP
+ RATESWSX-297: fix only-virtual-products-orders & add auto-delivery of virtual-products
+ RATESWSX-298: remove payment-method prepayment (existing orders can be still processed)

We also improved the code, functionality and API documentation. Cause these are not relevant for the changelog we didn't add them. [Full Changelog](https://github.com/ratepay/shopware6-module/compare/6.1.0...7.0.0)

## Version 6.1.0 - Released on 2023-11-10

* RATESWSX-270: fix fatal error if admin enter a debit/credit of zero
* RATESWSX-279: remove ratepay data for updated non-Ratepay-payment-method (e.g. if Ratepay-payment failed)
* RATESWSX-280: add functionality of feature flags
* RATESWSX-283: admin: add translation for user-profile search preferences
* RATESWSX-289: dfp: prevent output of token for admin users

## Version 6.0.0 - Released on 2023-05-11

* rebuilt assets for Shopware 6.5
* upgrade Code to PHP 8.1

## Version 5.0.0 - Released on 2023-05-11

* added Shopware 6.5 compatibility.

**Please note:**
This release-version is not installable in Shopware 6.5.
It contains a few improvements, which are also required for Shopware 6.5.
This release will likely be the last release for Shopware versions below 6.5.
If you want to upgrade to Shopware 6.5 you have to use the Plugin version 6.0.0.

## Version 4.2.1 - Released on 2023-02-02

* RATESWSX-261 - fixed: reloading profile-config (compatibility > 6.4.14.0)

## Version 4.2.0 - Released on 2022-07-21

* RATESWSX-249 - fixed: remove usage of deprecated EntityRepositoryInterface
* RATESWSX-248 - fixed: phonenumber can not be changed if the phone number got an initial validation error
* RATESWSX-248 - improvement: refill fields with user-entered values, if validation has been failed
* RATESWSX-250 - fixed: order panel: output translated tax-names for shopware default tax rules
* RATESWSX-251 - fixed: missing payment icons in checkout
* RATESWSX-253 - replace mandate information with small text
* RATESWSX-255 - improvement: exception handling during add-debit to order with invalid tax-rule

## Version 4.1.0 - Released on 2022-05-12

* RATESWSX-201 - added: display min rate for installment on product page
* RATESWSX-222 - add config help text
* RATESWSX-229 - improvement: add more filters to api log
* RATESWSX-231 - add compatibility to Bootstrap V5

## Version 4.0.1 - Released on 2022-04-28

* RATESWSX-242 - fix risk-management address compare (previously only the country has been compared)

## Version 4.0.0 - Released on 2022-03-31

* Compatibility with Shopware 6.4.9.x
* RATESWSX-201 - implement offline instalment calculator in checkout (rate calculation)
* RATESWSX-228 - administration: fix display of quantity in order-items panel
* RATESWSX-233 - improve class dependencies to improve the performance of class loading

## Version 3.3.0 - Released on 2022-02-11

* RATESWSX-221 - add missing icon and entity information on admin modules
* RATESWSX-223 - fix issues with loading the (multiple) profiles during payment filter
* RATESWSX-224 - fix loading of profile config during changing the payment method (update order)
* RATESWSX-225 - fix issues with the api-log search + make more fields searchable
* RATESWSX-225 - api log: search and filter improvements

## Version 3.2.0 - Released on 2022-01-10

* RATESWSX-214 - dfp: disable for admin orders

## Version 3.1.1 - Released on 2021-12-15

* RATESWSX-216 - fix issue that shipping tax has not been sent

## Version 3.1.0 - Released on 2021-12-09

* RATESWSX-211 - fix issue: submit gross price (instead of net price) if the prices are displayed in net.
* RATESWSX-212 - change system version in head

## Version 3.0.0 - Released on 2021-10-28

* RATESWSX-129 - add support for multiple profile ids for installment payments
* RATESWSX-176 - add functionality to send discounts and shipping costs as cart-item to ratepay gateway
* RATESWSX-205 - installment: add default payment selection
* RATESWSX-206 - admin: fix import of Shopware's apiService
* RATESWSX-209 - remove setters from entities

## Version 2.1.4 - Released on 2021-10-14

* RATESWSX-198 - fix datatypes of installment min-rate
* RATESWSX-199 - api-log: add reload button

## Version 2.1.3 - Released on 2021-09-08

* RATESWSX-196 - adds missing return statement

## Version 2.1.2 - Released on 2021-09-08

* RATESWSX-195 - migrations: remove dangerous destructive migration + fix NULL to NOT NULL
* RATESWSX-196 - add missing order filter to transition subscriber

## Version 2.1.1 - Released on 2021-08-11

The last release got not tagged correctly in GitHub. So we are forced to create a empty release with the correct tag.

## Version 2.1.0 - Released on 2021-08-05

* RATESWSX-190 - correct decorating AccountOrderController
* RATESWSX-191 - PaymentQuery: add missing vat-id
* RATESWSX-193 - fix error on instalment calculator after canceled payment

## Version 2.0.3 - Released on 2021-07-22

* RATESWSX-187 - Set default tracking-provider for confirmation deliver

## Version 2.0.2 - Released on 2021-07-22

* RATESWSX-145 - api log: improve output of XML
* RATESWSX-182 - checkout: add info for testmode
* RATESWSX-184 - order: fix error when a new version of an order got deleted
* RATESWSX-186 - installment calculator: fix error if user enter "0" as rate

## Version 2.0.1 - Released on 2021-06-15

* RATESWSX-181 - fix fatal error during b2b orders

## Version 2.0.0 - Released on 2021-06-10

* RATESWSX-173 - compatibility Shopware ^6.4 and PHP 8.0
* RATESWSX-178 - adds a few more columns to profile management

## Version 1.2.1 - Released on 2021-05-20

* RATESWSX-175 - fix collecting discounts

## Version 1.2.0 - Released on 2021-04-22

* RATESWSX-84 - adds functionality "admin orders"
* RATESWSX-115 - apply Shopware fix + increase Shopware min. compatibility
* RATESWSX-162 - add support for multiple tracking ids
* RATESWSX-169 - add account holder select for B2B accounts
* RATESWSX-173 - removes `configureRoutes` from bundle class
* RATESWSX-185 - fix foreign keys

## Version 1.1.0 - Released on 2021-02-15

* RATESWSX-149 - change translation of "date of birth"
* RATESWSX-150 - introduce payment query into payment flow
* RATESWSX-151 - fixed missing dependency during `administration:build` (`node_modules`)
* RATESWSX-157 - api-logs: add search function
* RATESWSX-158 - credit/debit: set custom tax rate
* RATESWSX-160 - add street-additional PaymentRequest operation
* RATESWSX-161 - add missing oder-id to operation requests
* RATESWSX-164 - add customer number to PaymentRequest operation

## Version 1.0.0

- initial release
