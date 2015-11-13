# 3. Stripe.Js. Create gateway. 

```php
<?php

use Payum\Stripe\StripeJsGatewayFactory;

$factory = new StripeJsGatewayFactory();

$gateway = $factory->create(array(
    'publishable_key' => 'aKey', 
    'secret_key' => 'aKey',
));
```

Back to [examples](index.md).
Back to [index](https://github.com/Payum/Core/tree/master/Resources/docs/index.md).