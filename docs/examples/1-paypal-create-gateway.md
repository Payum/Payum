<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

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
