# 1. Paypal. Create Payment. 

```php
<?php

$factory = new Payum\Paypal\ExpressCheckout\PaymentFactory();

$payment = $factory->create(array(
    'username' => 'aUsername',
    'password' => 'aPassword',
    'signature' => 'aSignature',
    'sandbox' => true,
));
```