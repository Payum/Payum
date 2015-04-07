# 1. Paypal. Create Gateway. 

```php
<?php

$factory = new Payum\Paypal\ExpressCheckout\PaypalExpressCheckoutGatewayFactory();

$gateway = $factory->create(array(
    'username' => 'aUsername',
    'password' => 'aPassword',
    'signature' => 'aSignature',
    'sandbox' => true,
));
```