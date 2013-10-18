# Have your cart? Want to use it?

###Write an action:

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

###Use it:

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
