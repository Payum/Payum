Payum Paypal ExpressCheckout Nvp
================================

The lib implements [Paypal Express Checkout](https://www.x.com/content/paypal-nvp-api-overview) payment.

## How to capture?

```php
<?php
//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::doCapture()
use Buzz\Client\Curl;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory;
use Payum\Request\CaptureRequest;
use Payum\Request\RedirectUrlInteractiveRequest;

$payment = PaymentFactory::create(new Api(new Curl, array(
    'username' => 'a_username',
    'password' => 'a_pasword',
    'signature' => 'a_signature',
    'sandbox' => true
)));

$capture = new CaptureRequest(array(
    'PAYMENTREQUEST_0_AMT' => 10,
    'PAYMENTREQUEST_0_CURRENCY' => 'USD',
    'RETURNURL' => 'http://foo.com/finishPayment',
    'CANCELURL' => 'http://foo.com/finishPayment',
));

if ($interactiveRequest = $payment->execute($capture, $expectsInteractive = true)) {
    //save your models somewhere.
    if ($interactiveRequest instanceof RedirectUrlInteractiveRequest) {
        header('Location: '.$interactiveRequest->getUrl());
        exit;
    }

    throw $interactiveRequest;
```

You can also capture digital goods:

```php
<?php
//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::doDigitalGoodsCapture()

$capture = new CaptureRequest(array(

    // ...

    'NOSHIPPING' => Api::NOSHIPPING_NOT_DISPLAY_ADDRESS,
    'REQCONFIRMSHIPPING' => Api::REQCONFIRMSHIPPING_NOT_REQUIRED,
    'L_PAYMENTREQUEST_0_ITEMCATEGORY0' => Api::PAYMENTREQUEST_ITERMCATEGORY_DIGITAL,
    'L_PAYMENTREQUEST_0_NAME0' => 'Awesome e-book',
    'L_PAYMENTREQUEST_0_DESC0' => 'Great stories of America.',
    'L_PAYMENTREQUEST_0_AMT0' => 10,
    'L_PAYMENTREQUEST_0_QTY0' => 1,
    'L_PAYMENTREQUEST_0_TAXAMT0' => 2,
));
```

## Was the payment finished successfully?

```php
<?php
//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::doStatus()
use Payum\Request\BinaryMaskStatusRequest;

$status = new BinaryMaskStatusRequest($capture->getModel());
$payment->execute($status);

if ($status->isSuccess()) {
    echo 'We are done';
}

echo "Hmm. We are not. Let's check other possible statuses!";
```

## Want to persist payments somewhere?

There are two storage supported out of the box. [doctrine2](https://github.com/Payum/Payum/blob/master/src/Payum/Bridge/Doctrine/Storage/DoctrineStorage.php)([offsite](http://www.doctrine-project.org/)) and [filesystem](https://github.com/Payum/Payum/blob/master/src/Payum/Storage/FilesystemStorage.php).
The filesystem storage is easy to setup, does not have any requirements. It is expected to be used more in tests.
To use doctrine2 storage you have to follow several steps:

* [Install](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/installation.html) doctrine2 lib.
* [Add](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/configuration.html#obtaining-an-entitymanager) mapping [schema](src/Payum/Paypal/ExpressCheckout/Nvp/Bridge/Doctrine/Resources/mapping/PaymentDetails.orm.xml) to doctrine configuration.
* Extend provided [model](src/Payum/src/Payum/Paypal/ExpressCheckout/Nvp/Bridge/Doctrine/Entity/PaymentDetails.php) and add `id` field.

_**Note:** Read payum's [how to persist payment details](https://github.com/Payum/Payum#how-to-persist-payment-details) chapter for more info._

## How about recurring payment?

First you have to create billing agreement and capture it as described [above](#how-to-capture).

```php
<?php
//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::createBillingAgrement()
use Payum\Request\CaptureRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

$captureBillingAgreement = new CaptureRequest(array(
    'PAYMENTREQUEST_0_AMT' => 0,
    'RETURNURL' => 'http://foo.com/finishPayment',
    'CANCELURL' => 'http://foo.com/finishPayment',
    'L_BILLINGTYPE0' => Api::BILLINGTYPE_RECURRING_PAYMENTS,
    'L_BILLINGAGREEMENTDESCRIPTION0' => 'Subsribe for weather forecast',
));

// ...
```

After you are done with capture, [check billing agreement status](#was-the-payment-finished-successfully). If it has success status create recurring payment:

```php
<?php
//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::createRecurringPaymnt()
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateRecurringPaymentProfileRequest;
use Payum\Request\SyncRequest;

$billingAgreementDetails = $captureBillingAgreement->getModel();

$recurringPaymentDetails = new \ArrayObject(array(
    'TOKEN' => $billingAgreementDetails['TOKEN'],
    'PROFILESTARTDATE' => date(DATE_ATOM),
    'DESC' => $billingAgreementDetails['L_BILLINGAGREEMENTDESCRIPTION0'],
    'AMT' => 1.45,
    'CURRENCYCODE' => 'USD',
    'BILLINGPERIOD' => Api::BILLINGPERIOD_DAY,
    'BILLINGFREQUENCY' => 2,
));

$payment->execute(
    new CreateRecurringPaymentProfileRequest($recurringPaymentDetails)
);
$payment->execute(new SyncRequest($recurringPaymentDetails));

$recurringPaymentStatus = new BinaryMaskStatusRequest($recurringPaymentDetails);
$payment->execute($recurringPaymentStatus);

if ($recurringPaymentStatus->isSuccess()) {
    echo 'We are done';
}

echo "Hmm. We are not. Let's check other possible statuses!";
```

## Have your cart? Want to use it?

Write an action:

```php
<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples\Action;

use Payum\Action\PaymentAwareAction;
use Payum\Request\CaptureRequest;
use Payum\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Examples\Model\AwesomeCart;

class CaptureAwesomeCartAction extends PaymentAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $cart = $request->getModel();

        $rawCaptureRequest = new CaptureRequest(array(
            'PAYMENTREQUEST_0_AMT' => $cart->getPrice(),
            'PAYMENTREQUEST_0_CURRENCY' => $cart->getCurrency(),
            'RETURNURL' => 'http://foo.com/finishPayment/'.$cart->getId(),
            'CANCELURL' => 'http://foo.com/finishPayment/'.$cart->getId(),
        ));

        $this->payment->execute($rawCaptureRequest);

        $cart->setPaymentDetails($rawCaptureRequest->getModel());
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof AwesomeCart
        ;
    }
}
```

Use it:

```php
<?php
//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::doCaptureAwesomeCart()
use Payum\Paypal\ExpressCheckout\Nvp\Examples\Model\AwesomeCart;
use Payum\Paypal\ExpressCheckout\Nvp\Examples\Action\CaptureAwesomeCartAction;

//...

$cart = new AwesomeCart;

$payment->addAction(new CaptureAwesomeCartAction);

$capture = new CaptureRequest($cart);
if ($interactiveRequest = $payment->execute($capture, $expectsInteractive = true)) {
    if ($interactiveRequest instanceof RedirectUrlInteractiveRequest) {
        header('Location: '.$interactiveRequest->getUrl());
        exit;
    }

    throw $interactiveRequest; //unexpected request
}

$status = new BinaryMaskStatusRequest($capture->getModel()->getPaymentDetails());
$payment->execute($status);

if ($status->isSuccess()) {
    echo 'We are done';
}

echo "Hmm. We are not. Let's check other possible statuses!";
```

## Like it? Spread the world!

You can star the lib on [github](https://github.com/Payum/PaypalExpressCheckoutNvp) or [packagist](https://packagist.org/packages/payum/paypal-express-checkout-nvp). You may also drop a message on Twitter.

## Need support?

If you are having general issues with [paypal express checkout](https://github.com/Payum/PaypalExpressCheckoutNvp) or [payum](https://github.com/Payum/Payum), we suggest posting your issue on [stackoverflow](http://stackoverflow.com/). Feel free to ping @maksim_ka2 on Twitter if you can't find a solution.

If you believe you have found a bug, please report it using the GitHub issue tracker: [paypal express checkout](https://github.com/Payum/PaypalExpressCheckoutNvp/issues) or [payum](https://github.com/Payum/Payum/issues), or better yet, fork the library and submit a pull request.

## License

Paypal express checkout is released under the MIT License. For more information, see [License](LICENSE).