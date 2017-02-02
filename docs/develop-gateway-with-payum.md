# Develop a custom Payum gateway.

This chapter could be useful for a developer who wants to create a gateway on top of payum.
The Payum provides a skeleton project which helps us a lots.

1. Create new project

```bash
$ composer create-project payum/skeleton
```

2. Replace all occurrences of `payum` with your vendor name. It may be your github name, for now let's say you choose: `acme`.
3. Replace all occurrences of `skeleton` with a payment gateway name. For example Stripe, Paypal etc. For now let's say you choose: `paypal`.
4. Register a gateway factory to the payum's builder and create a gateway:

```php
<?php

use Payum\Core\PayumBuilder;

$defaultConfig = [];

$payum = (new PayumBuilder)
    ->addGatewayFactory('paypal', new \Acme\Paypal\PaypalGatewayFactory($defaultConfig))

    ->addGateway('paypal', [
        'factory' => 'paypal',
        'sandbox' => true,
    ])

    ->getPayum()
;
```

5. While using the gateway implement all method where you get `Not implemented` exception:

```php
<?php

use Payum\Core\Request\Capture;

/** @var \Payum\Core\Payum $payum */
$paypal = $payum->getGateway('paypal');

$model = new \ArrayObject([
  // ...
]);

$paypal->execute(new Capture($model));
```

Back to [index](index.md).