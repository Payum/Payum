# Payum Silex Provider. Get it started.

In this chapter we are going to setup payum package and do simple purchase using paypal express checkout. 
Look at sandbox to find more examples.

## Installation

```bash
php composer.phar require payum/payum-silex-provider payum/xxx
```

_**Note**: Where payum/xxx is a payum package, for example it could be payum/paypal-express-checkout-nvp. Look at [supported gateways](../supported-gateways.md) to find out what you can use._

_**Note**: Use payum/payum if you want to install all gateways at once._

Now you have all codes prepared and ready to be used.

## Configuration

First add PayumProvider to your application:

```php
<?php
//payum provider requires some other providers to be registered.
$app->register(new \Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new \Silex\Provider\FormServiceProvider());
$app->register(new \Silex\Provider\TranslationServiceProvider());
$app->register(new \Silex\Provider\TwigServiceProvider());
$app->register(new \Silex\Provider\ValidatorServiceProvider());
$app->register(new \Silex\Provider\ServiceControllerServiceProvider());

$app->register(new \Payum\Silex\PayumProvider());
```

Now you can configure the gateway gateway and the storages:

```php
<?php
use Payum\Core\Storage\FilesystemStorage;

$app['payum.security.token_storage'] = $app->share(function($app) {
    return new FilesystemStorage('/path/to/storage', 'Payum\Core\Model\Token', 'hash'),
});

$app['payum.gateways'] = $app->share($app->extend('payum.gateways', function ($gateways) use ($app) {
    $paypalExpressCheckoutGatewayFactory = new \Payum\Paypal\ExpressCheckout\Nvp\PaypalExpressCheckoutGatewayFactory();

    $gateways['paypal_ec'] = $paypalExpressCheckoutGatewayFactory->create(array(
        'username' => 'EDIT_ME',
        'password' => 'EDIT_ME',
        'signature' => 'EDIT_ME',
        'sandbox' => true
    ));
    
    return $gateways;
});

$app['payum.storages'] = $app->share($app->extend('payum.storages', function ($storages) use ($app) {
    $storages['Payum\Core\Model\Payment'] = new FilesystemStorage('path/to/storage', 'Payum\Core\Model\Payment');
    
    return $storages;
});
```

## Prepare payment

Lets create a controller where we prepare the payment details.

```php
<?php
class PaymentController
{
    protected $app;

    public function __constructor(Application $app)
    {
        $this->app = $app;
    }

	public function preparePaypalAction()
	{
        $storage = $this->app['payum']->getStorage('Payum\Core\Model\Payment');

        $payment = $storage->create();
        $payment->setTotalAmount(123);
        $payment->setCurrencyCode('USD');
        $storage->update($details);

        $captureToken = $this->app['payum.security.token_factory']->createCaptureToken('paypal_ec', $payment, 'payment_done');

        return new RedirectResponse($captureToken->getTargetUrl());
	}
}
```

Here's you may want to modify a `payment_done` route. 
It is a controller where the a payer will be redirected after the payment is done, whenever it is success failed or pending. 
Read a [dedicated chapter](payment-done-controller.md) about how the payment done controller may look like.

Back to [index](../index.md).