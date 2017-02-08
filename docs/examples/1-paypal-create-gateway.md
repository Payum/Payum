# 1. Paypal. Create Gateway. 

```php
<?php

use Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory;

$factory = new PaypalExpressCheckoutGatewayFactory();

$gateway = $factory->create(array(
    'username' => 'aUsername',
    'password' => 'aPassword',
    'signature' => 'aSignature',
    'sandbox' => true,
));
```

Back to [examples](index.md).
Back to [index](../index.md).
