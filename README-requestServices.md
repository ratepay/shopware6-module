## Request services

### General

All requests services are child classes of `\Ratepay\RpayPayments\Components\RatepayApi\Service\Request\AbstractRequest`.

You need an instance of the abstract class `\Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData` (in the
example below we will use the class `PaymentInitData`) as parameter for the `execute`-method of the RequestService class.

#### Notes:

- The data for the request will be automatically built by the different data-factories.
  Please have a look into each RequestService to find out, which factories are used. Please also have a look into the
  different factories.
- You not need to log any request to the api-log table. Any request will be logged automatically.
- Important: All RequestServices dispatches different events. The Ratepay-Extension already have a few subscribers on
  these events. But also third party subscribers could be listen on these events. So please make sure, if you use the
  RequestServices, that all third party extensions works properly.

**Please note:** Please do not send manually changes of an order to the gateway. All changes, which are made not via the
extension, will be not visible for the extension.

### ProfileRequestService

Use this request to get the profile configuration from the Ratepay gateway.

|||
|---|---|
| Service Class    | `\Ratepay\RpayPayments\Components\RatepayApi\Service\Request\ProfileRequestService` |
| Data Class        | `\Ratepay\RpayPayments\Components\RatepayApi\Dto\ProfileRequestData` |
| Response Class    | `\RatePAY\Model\Response\ProfileRequest` |

#### Example

```php
/** @var \Shopware\Core\Framework\Context $context **/
/** @var \Ratepay\RpayPayments\Components\RatepayApi\Service\Request\ProfileRequestService $requestService **/
/** @var \Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity $profileConfig **/

$requestData = new \Ratepay\RpayPayments\Components\RatepayApi\Dto\ProfileRequestData($context, $profileConfig);
$requestBuilder = $requestService->doRequest($requestData);

/** @var \RatePAY\Model\Response\ProfileRequest $response */
$response = $requestBuilder->getResponse();

if($response->isSuccessful()) {
    // do something if the request is successful
    /** @var array $result */
    $result = $response->getResult(); // this will contains all information to the profile
}

```

### PaymentInitService

Use this request to initialize a new payment for a customer.

**Please note** that the transaction ID is related to the profile-id. You can not use it for requests which are made
with another profile-id.

This request does not need any parameters. (except the profileConfig)

|||
|---|---|
| Service Class    | `\Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentInitService` |
| Data Class        | `\Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentInitData` |
| Response Class    | `\RatePAY\Model\Response\PaymentInit` |

#### Example

```php
/** @var \Shopware\Core\Framework\Context $context **/
/** @var \Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentInitService $requestService **/
/** @var \Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity $profileConfig **/

$requestData = new \Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentInitData($profileConfig, $context);
$requestBuilder = $requestService->doRequest($requestData);

/** @var \RatePAY\Model\Response\PaymentInit $response */
$response = $requestBuilder->getResponse();

if($response->isSuccessful()) {
    // do something if the request is successful
    $transactionId = $response->getTransactionId();
}

```

### PaymentQueryService

Use this request to get the available payment methods for the current cart/customer

You must not load the profile config. It will be loaded automatically.

**Please note:** maybe this request will be removed in a future release (depends on shopware changes).

|||
|---|---|
| Service Class    | `\Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Service\Request\PaymentQueryService` |
| Data Class        | `\Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto\PaymentQueryData` |
| Response Class    | `\RatePAY\Model\Response\PaymentQuery` |

#### Example

```php
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;

/** @var \Shopware\Core\System\SalesChannel\SalesChannelContext $salesChannelContext **/
/** @var \Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Service\Request\PaymentQueryService $requestService **/

// use \Shopware\Core\Checkout\Cart\SalesChannel\CartService::getCart() to the cart of the current context
/** @var \Shopware\Core\Checkout\Cart\Cart $cart **/

// the parameters differ between b2c and b2b
$requestDataBag = new RequestDataBag([
    // required for b2c
    'birthday' => new RequestDataBag([
        'year' => '2000',
        'month' => '1',
        'day' => '30',
    ]),
    // required for b2b
    'vatId' => 'DE123456789',
]);

$ratepayTransactionId = '12-3456789123456789';

$requestData = new \Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto\PaymentQueryData(
    $salesChannelContext,
    $cart,
    $requestDataBag,
    $ratepayTransactionId
);
$requestBuilder = $requestService->doRequest($requestData);

/** @var \RatePAY\Model\Response\PaymentQuery $response */
$response = $requestBuilder->getResponse();

if($response->isSuccessful()) {
    // do something if the request is successful
    $admittedPaymentMethods = $response->getAdmittedPaymentMethods();
}
```

### PaymentRequestService

Use this request to create a payment for an order.

The subscribers of this request will automatically create the order extension for the entity, so that all follow-up operations can be done.

So you don't have to create any data manually.

You also don't have to load the profile config. It will be loaded automatically.

|||
|---|---|
| Service Class    | `\Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentRequestService` |
| Data Class        | `\Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData` |
| Response Class    | `\RatePAY\Model\Response\PaymentRequest` |

#### Example

```php
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;

/** @var \Shopware\Core\System\SalesChannel\SalesChannelContext $salesChannelContext **/
/** @var \Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentRequestService $requestService **/

// you need a already persisted order entity
/** @var \Shopware\Core\Checkout\Order\OrderEntity $orderEntity **/
// you need a already persisted transaction entity, which is associated to the order.
/** @var \Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity $transactionEntity **/

// the parameters differ between b2c and b2b
$requestDataBag = new RequestDataBag([
    // required for b2c
    'birthday' => new RequestDataBag([
        'year' => '2000',
        'month' => '1',
        'day' => '30',
    ]),
    // required for b2b
    'vatId' => 'DE123456789',

    // require if payment method is DIRECT-DEBIT
    'bankData' => new RequestDataBag([
        'accountHolder' => 'Max Mustermann',
        'iban' => 'DE12 3456 7891 2345 6789 12',
    ]),
]);

$ratepayTransactionId = '12-3456789123456789';

$requestData = new \Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData(
    $salesChannelContext,
    $orderEntity,
    $transactionEntity,
    $requestDataBag,
    $ratepayTransactionId
);

$requestBuilder = $requestService->doRequest($requestData);

/** @var \RatePAY\Model\Response\PaymentRequest $response */
$response = $requestBuilder->getResponse();

if($response->isSuccessful()) {
    // do something if the request is successful
    $descriptor = $response->getDescriptor();
}
```

### Order item operations (deliver, cancel, return)
Use these request to mark one or more items (line-items, shipping costs, discounts) of the order as "delivered", "canceled" or "returned"

All request does have the same syntax, and functionalities. So we define it only once in this document.

The examples are for the delivery of items.

#### Deliver
|||
|---|---|
| Service Class     | `\Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentDeliverService` |
| Data Class        | `\Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData` |
| Response Class    | `\RatePAY\Model\Response\ConfirmationDeliver` |

#### Cancel
|||
|---|---|
| Service Class     | `\Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentCancelService` |
| Data Class        | `\Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData` |
| Response Class    | `\RatePAY\Model\Response\PaymentChange` |

#### Return
|||
|---|---|
| Service Class     | `\Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentReturnService` |
| Data Class        | `\Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData` |
| Response Class    | `\RatePAY\Model\Response\PaymentChange` |

#### Example #1

Use this example to deliver all (possible) items of the order.

```php
/** @var \Shopware\Core\Framework\Context $context **/
/** @var \Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentDeliverService $requestService **/

// the order to process
/** @var \Shopware\Core\Checkout\Order\OrderEntity $orderEntity **/

$requestData = new \Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData(
    $context,
    $orderEntity,
    \Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData::OPERATION_DELIVER
);

$requestBuilder = $requestService->doRequest($requestData);

/** @var \RatePAY\Model\Response\ConfirmationDeliver $response */
$response = $requestBuilder->getResponse();

if($response->isSuccessful()) {
    // do something if the request is successful
}
```

#### Example 2

in this example you see how to partly deliver/cancel/return the order.

You always need to have the UUIDs of each line item.

```php
/** @var \Shopware\Core\Framework\Context $context **/
/** @var \Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentDeliverService $requestService **/

// the order to process
/** @var \Shopware\Core\Checkout\Order\OrderEntity $orderEntity **/

$requestData = new \Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData(
    $context,
    $orderEntity,
    \Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData::OPERATION_DELIVER,
    [
        'uuid-of-line-item #1' => 1, // the value is the qty of the count of items, which should be delivered/canceled/return,
        'uuid-of-line-item #2' => 5,
        'uuid-of-line-item #3' => 2,
        \Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData::ITEM_ID_SHIPPING => 1, // use this to use the shipping-position in this operation.
    ]
);

$requestBuilder = $requestService->doRequest($requestData);

/** @var \RatePAY\Model\Response\ConfirmationDeliver $response */
$response = $requestBuilder->getResponse();

if($response->isSuccessful()) {
    // do something if the request is successful
}
```

#### Example 3

in this example you will see how to update stocks on return/cancel.

This make sense if you want to automatically re-add the items to your stock after a return/cancel.

```php
/** @var \Shopware\Core\Framework\Context $context **/
/** @var \Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentCancelService $requestService **/

// the order to process
/** @var \Shopware\Core\Checkout\Order\OrderEntity $orderEntity **/

$requestData = new \Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData(
    $context,
    $orderEntity,
    \Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData::OPERATION_CANCEL,
    null, // optional: add the line items/shipping/discount positions
    true // set this flag to `true`, if you want to update the stock. set it to `false`, if not. (default is `true`)
);

$requestBuilder = $requestService->doRequest($requestData);

/** @var \RatePAY\Model\Response\PaymentChange $response */
$response = $requestBuilder->getResponse();

if($response->isSuccessful()) {
    // do something if the request is successful
}
```

## PaymentCreditService

Use this service to add a debit or a credit to the order.

**Please note:** you don't have to add the debit/credit to the order before. it will be added automatically.
**Please note:** you don't have to deliver the credit/debit item. It is already marked as delivered on the Ratepay gateway.

|||
|---|---|
| Service Class     | `\Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentCreditService` |
| Data Class        | `\Ratepay\RpayPayments\Components\RatepayApi\Dto\AddCreditData` |
| Response Class    | `\RatePAY\Model\Response\PaymentChange` |

## Example

```php
/** @var \Shopware\Core\Framework\Context $context **/
/** @var \Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentDeliverService $requestService **/

// the order to process
/** @var \Shopware\Core\Checkout\Order\OrderEntity $orderEntity **/

$requestData = new \Ratepay\RpayPayments\Components\RatepayApi\Dto\AddCreditData(
    $context,
    $orderEntity,
    'Name of the Credit/Debit on the invoice',  // Please note: use the correct translation. This will not get automatically translated
    10.50, // gross amount of the item. Can be also negative for a credit.
    7.7 // tax rate for tax calculation
);

$requestBuilder = $requestService->doRequest($requestData);

/** @var \RatePAY\Model\Response\PaymentChange $response */
$response = $requestBuilder->getResponse();

if($response->isSuccessful()) {
    // do something if the request is successful
}
```
