# 3. Stripe.Js. Create payment. 

```php
<?php

$factory = new Payum\Stripe\PaymentJsFactory();

$payment = $factory->create(array(
    'publishable_key' => 'aKey', 
    'secret_key' => 'aKey',
));
```