# Get it started. Paypal ExpressCheckout.

In this chapter we are going to talk about the most common task: purchase of a product using [Paypal ExpressCheckout](https://www.paypal.com/webapps/mpp/express-checkout).
We assume you already read [get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md) from core.
Here we just show you modifications you have to put to the files shown there.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require payum/paypal-express-checkout-nvp
```

## config.php

We only have to add the factory factory. All the rest remains the same:

```php
<?php
//config.php

use Payum\Core\Extension\GenericTokenFactoryExtension;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

$factory = new \Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory; 
$gateways['paypal_express_checkout'] = $factory->create(array(
   'username'  => 'change it',
   'password'  => 'change it',
   'signature' => 'change it',
   'sandbox'   => true,
   
   // uncomment if you want notify url to be generated automatically.
   // 'payum.extension.token_factory' => new GenericTokenFactoryExtension($tokenFactory), 
));
```

## prepare.php

Here you have to modify the `gatewayName` value. Set it to `paypal_express_checkout`. The rest remain the same as described basic [get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md) documentation.

## Next 

* [Core's Get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md).
* [The architecture](https://github.com/Payum/Core/blob/master/Resources/docs/the-architecture.md).
* [Supported gateways](https://github.com/Payum/Core/blob/master/Resources/docs/supported-gateways.md).
* [Storages](https://github.com/Payum/Core/blob/master/Resources/docs/storages.md).
* [Capture script](https://github.com/Payum/Core/blob/master/Resources/docs/capture-script.md).
* [Authorize script](https://github.com/Payum/Core/blob/master/Resources/docs/authorize-script.md).
* [Done script](https://github.com/Payum/Core/blob/master/Resources/docs/done-script.md).

Back to [index](index.md).
