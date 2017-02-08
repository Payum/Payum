# Paypal Express Checkout. Authorize order.

Authorization & Capture, or Auth/Capture, allows you to authorize the availability of funds for a transaction but delay the capture of funds until a later time.
This is often useful for merchants who have a delayed order fulfillment process.
Authorize & Capture also enables merchants to modify the original authorization amount due to order changes occurring after the initial order is placed, such as taxes, shipping or gratuity.

```php
<?php
// demo.php

use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Payum\Core\Request\Authorize;

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addGateway('paypal', [
        'factory' => 'paypal_express_checkout',
        'username' => 'edit me',
        'password' => 'edit me',
        'signature' => 'edit me'
    ])

    ->getPayum()
;

$payum->getGateway('paypal')->execute(new Authorize([
    'PAYMENTREQUEST_0_AMT' => 1.1,
    'PAYMENTREQUEST_0_CURRENCY' => 'USD',
]));
```

Or you can create a token and reuse authorize script:

```php
<?php

use Payum\Core\Model\Payment;

/** @var \Payum\Core\Payum $payum */
/** @var array|\ArrayObject|Payment $payment */

$authorizeToken = $payum->getTokenFactory()->createAuthorizeToken('paypal', $payment, 'http://afterUrl');

header("Location: ".$authorizeToken->getTargetUrl());
```

Back to [index](../../index.md).
