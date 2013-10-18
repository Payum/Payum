# Was the payment finished successfully?

```php
<?php

//...
use Payum\Request\BinaryMaskStatusRequest;

$statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
$payment->execute($statusRequest);
if ($statusRequest->isSuccess()) {
    echo 'We are done';
}

echo "Hmm. We are not. Let's check other possible statuses!";
```