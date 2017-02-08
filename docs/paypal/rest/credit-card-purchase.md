# Paypal Rest. Credit card purchase.

In [get it started](get-it-started.md) chapter we showed how you can setup payment with authorization on the paypal side.
Here we show how to purchase something using credit card provided by a user.

## Prepare payment

```php
<?php

include __DIR__.'/config.php';

use PayPal\Api\Address;
use PayPal\Api\Amount;
use PayPal\Api\CreditCard;
use PayPal\Api\Payer;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Transaction;

/** @var \Payum\Core\Payum $payum */
$storage = $payum->getStorage($paypalRestPaymentDetailsClass);

$payment = $storage->create();
$storage->update($payment);

$address = new Address();
$address->line1 = "3909 Witmer Road";
$address->line2 = "Niagara Falls";
$address->city = "Niagara Falls";
$address->state = "NY";
$address->postal_code = "14305";
$address->country_code = "US";
$address->phone = "716-298-1822";

$card = new CreditCard();
$card->type = "visa";
$card->number = "4417119669820331";
$card->expire_month = "11";
$card->expire_year = "2019";
$card->cvv2 = "012";
$card->first_name = "Joe";
$card->last_name = "Shopper";
$card->billing_address = $address;

$fi = new FundingInstrument();
$fi->credit_card = $card;

$payer = new Payer();
$payer->payment_method = "credit_card";
$payer->funding_instruments = array($fi);

$amount = new Amount();
$amount->currency = "USD";
$amount->total = "1.00";

$transaction = new Transaction();
$transaction->amount = $amount;
$transaction->description = "This is the payment description.";

$payment->intent = "sale";
$payment->payer = $payer;
$payment->transactions = array($transaction);

$captureToken = $payum->getTokenFactory()->createCaptureToken('paypalRest', $payment, 'create_recurring_payment.php');

header("Location: ".$captureToken->getTargetUrl());
```

Back to [index](../../index.md).