# Was the payment finished successfully?

```php
<?php
//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::doStatus()
use Payum\Request\BinaryMaskStatusRequest;

$status = new BinaryMaskStatusRequest($capture->getModel());
$payment->execute($status);

if ($status->isSuccess()) {
    echo 'We are done';
}

echo "Hmm. We are not. Let's check other possible statuses!";
```
