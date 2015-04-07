# 3. Stripe.Js. Create gateway. 

```php
<?php

$factory = new Payum\Stripe\StripeJsGatewayFactory();

$gateway = $factory->create(array(
    'publishable_key' => 'aKey', 
    'secret_key' => 'aKey',
));
```