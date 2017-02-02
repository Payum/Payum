# Payum Laravel Package. Examples

## Paypal Express checkout

Described in [Get it started](get-it-started.md)

## Payment model

* Configuration

```bash
$ php composer.phar require payum/payum-laravel-package payum/offline
```

```php
// bootstrap/start.php

App::resolving('payum.builder', function(\Payum\Core\PayumBuilder $payumBuilder) {
    $payumBuilder
        ->addGateway('offline', ['factory' => 'offline'])
    ;
});
```

* Prepare payment

```php
<?php
// app/controllers/PaymentController.php

use Payum\LaravelPackage\Controller\PayumController;

cclass PaymentController extends PayumController
{
 	public function preparePayment()
 	{
         $storage = $this->getPayum()->getStorage('Payum\Core\Model\Payment');

         $payment = $storage->create();
         $payment->setNumber(uniqid());
         $payment->setCurrencyCode('EUR');
         $payment->setTotalAmount(123); // 1.23 EUR
         $payment->setDescription('A description');
         $payment->setClientId('anId');
         $payment->setClientEmail('foo@example.com');
         $payment->setDetails(array(
           // put here any fields in a gateway format.
           // for example if you use Paypal ExpressCheckout you can define a description of the first item:
           // 'L_PAYMENTREQUEST_0_DESC0' => 'A desc',
         ));
         $storage->update($payment);

         $captureToken = $payum->getTokenFactory()->createCaptureToken('offline', $payment, 'payment_done');

         return \Redirect::to($captureToken->getTargetUrl());
 	}
}
```

## Stripe.Js

* Configuration

```bash
$ php composer.phar require payum/payum-laravel-package stripe/stripe-php payum/stripe
```

```php
// bootstrap/start.php

App::resolving('payum.builder', function(\Payum\Core\PayumBuilder $payumBuilder) {
    $payumBuilder
        ->addGateway('stripe_js', [
            'factory' => 'stripe_js',
            'publishable_key' => 'EDIT ME',
            'secret_key' => 'EDIT ME',
         ])
    ;
});
```

* Prepare payment

```php
<?php
// app/controllers/StripeController.php

use Payum\LaravelPackage\Controller\PayumController;

cclass StripeController extends PayumController
{
 	public function prepareJs()
 	{
         $storage = $this->getPayum()->getStorage('Payum\Core\Model\ArrayObject');
 
         $details = $storage->create();
         $details['amount'] = '100';
         $details['currency'] = 'USD';
         $details['description'] = 'a desc';
         $storage->update($details);
 
         $captureToken = $this->getPayum()->getTokenFactory()->createCaptureToken('stripe_js', $details, 'payment_done');
 
         return \Redirect::to($captureToken->getTargetUrl());
 	}
}
```

## Stripe Checkout

* Configuration

```bash
$ php composer.phar require payum/stripe payum/payum-laravel-package stripe/stripe-php
```

```php
// bootstrap/start.php

App::resolving('payum.builder', function(\Payum\Core\PayumBuilder $payumBuilder) {
    $payumBuilder
        ->addGateway('stripe_checkout', [
            'factory' => 'stripe_checkout',
            'publishable_key' => 'EDIT ME',
            'secret_key' => 'EDIT ME',
         ])
    ;
});
```

* Prepare payment

```php
<?php
// app/controllers/StripeController.php

use Payum\LaravelPackage\Controller\PayumController;

cclass StripeController extends PayumController
{
 	public function prepareCheckout()
 	{
         $storage = $this->getPayum()->getStorage('Payum\Core\Model\ArrayObject');
 
         $details = $storage->create();
         $details['amount'] = '100';
         $details['currency'] = 'USD';
         $details['description'] = 'a desc';
         $storage->update($details);
 
         $captureToken = $this->getPayum()->getTokenFactory()->createCaptureToken('stripe_checkout', $details, 'payment_done');
 
         return \Redirect::to($captureToken->getTargetUrl());
 	}
}
```

## Stripe Direct (via Omnipay)

* Configuration

```bash
$ php composer.phar require payum/omnipay-bridge payum/payum-laravel-package omnipay/stripe
```

```php
// bootstrap/start.php

App::resolving('payum.builder', function(\Payum\Core\PayumBuilder $payumBuilder) {
    $payumBuilder
        ->addGateway('stripe_direct', [
            'factory' => 'omnipay_direct',
            'type' => 'Stripe',
            'options' => array(
                'apiKey' => 'EDIT ME',
                'testMode' => true,
            ),
         ])
    ;
});
```

* Prepare payment

```php
<?php
// app/controllers/OmnipayController.php

use Payum\LaravelPackage\Controller\PayumController;

cclass OmnipayController extends PayumController
{
 	public function prepareDirect()
 	{
         $storage = $this->getPayum()->getStorage('Payum\Core\Model\ArrayObject');
 
         $details = $storage->create();
         $details['amount'] = '10.00';
         $details['currency'] = 'USD';
         $storage->update($details);
 
         $captureToken = $this->getPayum()->getTokenFactory()->createCaptureToken('stripe_direct', $details, 'payment_done');
 
         return \Redirect::to($captureToken->getTargetUrl());
 	}
}
```

Back to [index](../index.md).