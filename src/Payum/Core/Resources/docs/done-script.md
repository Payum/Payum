## Done script

After the capture did its job you will be redirected to `done.php`.
The [`capture.php`](capture-script.md) script always redirects you to `done.php` no matter the payment was a success or not.
In `done.php` we will check the payment status and act on its result. We can dispatch events, store status somewhere etc.

```php
<?php
// done.php

use Payum\Core\Request\SimpleStatusRequest;

include 'config.php';

$token = $requestVerifier->verify($_REQUEST);
// $requestVerifier->invalidate($token);

$payment = $registry->getPayment($token->getPaymentName());

$payment->execute($status = new SimpleStatusRequest($token));

header('Content-Type: application/json');
echo json_encode(array(
    'status' => $status->getStatus(),
    'details' => iterator_to_array($status->getModel())
)));
```

_**Note**: We advice you to invalidate(remove) the token as soon as you do not need it. It would be good to redirect a user from this url to a safe one._

Back to [index](index.md).