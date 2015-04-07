## Done script

After the capture did its job you will be redirected to `done.php`.
The [capture.php](capture-script.md) script always redirects you to `done.php` no matter the payment was a success or not.
In `done.php` we will check the payment status and act on its result. We can dispatch events, store status somewhere etc.

```php
<?php
// done.php

use Payum\Core\Request\GetHumanStatus;

include 'config.php';

$token = $requestVerifier->verify($_REQUEST);

$identity = $token->getDetails();
$model = $payum->getStorage($identity->getClass())->find($identity);

$gateway = $payum->getGateway($token->getPaymentName());

// you can invalidate the token. The url could not be requested any more.
// $requestVerifier->invalidate($token);

// Once you have token you can get the model from the storage directly. 
//$identity = $token->getDetails();
//$model = $payum->getStorage($identity->getClass())->find($identity);

// or Payum can fetch the model for you while executing a request (Preferred).
$gateway->execute($status = new GetHumanStatus($token));
$model = $status->getFirstModel());

header('Content-Type: application/json');
echo json_encode(array(
    'status' => $status->getValue(),
    'details' => iterator_to_array($model)
)));
```

_**Note**: We advice you to invalidate(remove) the token as soon as you do not need it. It would be good to redirect a user from this url to a safe one._

Back to [index](index.md).