<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# 3. Stripe.Js. Create gateway. 

```php
<?php

use Payum\Stripe\StripeJsGatewayFactory;

$factory = new StripeJsGatewayFactory();

$gateway = $factory->create([
    'publishable_key' => 'aKey', 
    'secret_key' => 'aKey',
]);
```

Back to [examples](index.md).
Back to [index](../index.md).