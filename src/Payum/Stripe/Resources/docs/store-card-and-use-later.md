# Store credit card and use later.

In this chapter we show how to store a credit card safely and use in future.
A customer enter the card once and billed later without the need to reenter it again.

## Store card

```php
<?php
// prepare.php

/** @var \Payum\Core\Model\PaymentInterface $payment */

$payment->setDetails([
    // everything in this section is never sent to the payment gateway
    'local' => [
        'save_card' => true,
    ],
]);
```

once the first payment is done you can get the customer id and store it somewhere

```php
<?php
// done.php

use Payum\Core\Request\GetCreditCardToken;

/** @var \Payum\Core\Model\PaymentInterface $payment */


$gateway->execute($getToken = new GetCreditCardToken($payment));

$token = $getToken->getToken(); // if not null you are done. store it somewhere
```

## Use stored card

```php
<?php
// prepare.php

use Payum\Core\Model\CreditCard;

/** @var \Payum\Core\Model\PaymentInterface $payment */

$card = new CreditCard();
$card->setToken($token);
$payment->setCreditCard($card);

// capture the payment
```

## Links

* https://support.stripe.com/questions/can-i-save-a-card-and-charge-it-later
* https://stripe.com/docs/charges

Back to [index](index.md).
