# Stripe. Subscription billing.

In this chapter we show how to create subscription plan and use it in future to charge a customer.

## Create a plan

```php
<?php
// prepare.php

use Payum\Stripe\Request\Api\CreatePlan;

$plan = new \ArrayObject([
    "amount" => 2000,
    "interval" => "month",
    "name" => "Amazing Gold Plan",
    "currency" => "usd",
    "id" => "gold"
]);

/** @var \Payum\Core\Payum $payum */
$payum->getGateway('gatewayName')->execute(new CreatePlan($plan));
```

## Subscribing a customer to a plan

This is a usual charge as we showed it in [get-it-started](../get-it-started.md) with only these additions:

```php
<?php
// prepare.php

/** @var \Payum\Core\Model\PaymentInterface $payment */

$payment->setDetails(new \ArrayObject([
    'amount' => 2000,
    'currency' => 'USD',

    // everything in this section is never sent to the payment gateway
    'local' => [
        'save_card' => true,
        'customer' => ['plan' => $plan['id']],
    ],
]));
```

## Links

* https://stripe.com/docs/subscriptions/tutorial

Back to [index](../index.md).
