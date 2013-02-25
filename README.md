Payum Paypal ExpressCheckout Nvp
================================

The lib implements [Paypal Express Checkout](https://www.x.com/content/paypal-nvp-api-overview) payment.

## How to capture?

```php
<?php
use Buzz\Client\Curl;

use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Payment;
use Payum\Request\CaptureRequest;
use Payum\Request\RedirectUrlInteractiveRequest;

//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::doCapture()

$payment = Payment::create(new Api(new Curl, array(
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
}
// ...
```

## Was the payment finished successfully?

```php
<?php
use Payum\Request\BinaryMaskStatusRequest;

//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::doStatus()

//...

$status = new BinaryMaskStatusRequest($capture->getModel());
$payment->execute($status);

if ($status->isSuccess()) {
    echo 'We are done';
}

echo "Hmm. We are not. Let's check other possible statuses!";
```

## Want to store payments somewhere?

There are two storage supported out of the box. [doctrine2](https://github.com/Payum/Payum/blob/master/src/Payum/Bridge/Doctrine/Storage/DoctrineStorage.php)([offsite](http://www.doctrine-project.org/)) and [filesystem](https://github.com/Payum/Payum/blob/master/src/Payum/Storage/FilesystemStorage.php).
The filesystem storage is easy to setup, does not have any requirements. It is expected to be used more in tests. 
To use doctrine2 storage you have to follow several steps:

* [Install](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/installation.html) doctrine2 lib. 
* [Add](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/configuration.html#obtaining-an-entitymanager) mapping [schema](src/Payum/Paypal/ExpressCheckout/Nvp/Bridge/Doctrine/Resources/mapping/PaymentInstruction.orm.xml) to doctrine configuration. 
* Extend provided [model](src/Payum/src/Payum/Paypal/ExpressCheckout/Nvp/Bridge/Doctrine/Entity/PaymentInstruction.php) and add `id` field.

Want another storage? Contribute!

## Have your cart? Want to use it? No problem!

Write an action:

```php
<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples\Action;

use Payum\Request\CaptureRequest;
use Payum\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\ActionPaymentAware;
use Payum\Paypal\ExpressCheckout\Nvp\Examples\Model\AwesomeCart; 

class CaptureAwesomeCartAction extends ActionPaymentAware
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
use Payum\Paypal\ExpressCheckout\Nvp\Examples\Model\AwesomeCart;
use Payum\Paypal\ExpressCheckout\Nvp\Examples\Action\CaptureAwesomeCartAction;

//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::doCaptureAwesomeCart()

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