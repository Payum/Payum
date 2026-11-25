# Subscription Billing

In this chapter we show how to create subscription with [Prices API](https://docs.stripe.com/api/prices).

### Create a product

```php
<?php
// prepare.php

use Payum\Stripe\Request\Api\CreateProduct;

$product = new \ArrayObject([
    "name"  => "Amazing Gold Plan",
]);

/** @var \Payum\Core\Payum $payum */
$payum->getGateway('gatewayName')->execute(new CreateProduct($product));
```

### Create a price

```php
<?php
// prepare.php

use Payum\Stripe\Request\Api\CreatePrice;

The recurring components of a price such as interval and interval_count .
The number of intervals between subscription billings. For example, interval=month and interval_count=3 bills every 3 months. Maximum of one year interval allowed (1 year, 12 months, or 52 weeks).

$price = new \ArrayObject([
    "product" => "prod_NjpI7DbZx6AlWQ", // Product ID
    "unit_amount" => 2000,
    "currency" => "usd",
    "recurring" => [
        "interval" => "month",
        "interval_count" => 3,
    ],
]);

/** @var \Payum\Core\Payum $payum */
$payum->getGateway('gatewayName')->execute(new CreatePrice($price));
```

### Subscribing a customer to a plan(price)

This is a usual charge as we showed it in [get-it-started](../get-it-started.md) with only these additions:

```php
<?php
// prepare.php

use Payum\Stripe\Request\Api\CreateSubscription;

$subscriptionRequest = new \ArrayObject([
    'customer' => "cus_Q9Q0EMVUcueBUB", // Customer ID
    'items'     => [
        [
            'price' => "price_1OKlckCozROjz2jXrQqMaU0N", // Price ID
        ]
    ],
]);

/** @var \Payum\Core\Payum $payum */
$payum->getGateway('gatewayName')->execute(new CreateSubscription($subscriptionRequest));
```

### Links

* [https://stripe.com/docs/subscriptions/tutorial](https://stripe.com/docs/subscriptions/tutorial)

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/Payum)
