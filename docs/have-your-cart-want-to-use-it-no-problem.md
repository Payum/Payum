# Have your cart? Want to use it? No problem!

### Write an action:

```php
<?php
namespace Foo\Payum\Action;

use Payum\Action\PaymentAwareAction;
use Payum\Request\CaptureRequest;
use Payum\Exception\RequestNotSupportedException;

use Foo\AwesomeCart;

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

        $rawCaptureRequest = $captureRequest = new CaptureRequest(array(
            'amount' => $cart->getAmount(),
            'card_num' => $cart->getCreditCard()->getNumber(),
            'exp_date' => $cart->getCreditCard()->getExpirationDate(),
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

### Use it:

```php
<?php
//...

use Payum\Request\CaptureRequest;
use Payum\Request\BinaryMaskStatusRequest;

use Foo\Payum\Action\CaptureAwesomeCartAction;
use Foo\AwesomeCart;

$payment->addAction(new CaptureAwesomeCartAction);

$payment->execute(new CaptureRequest($cart);

$statusRequest = new BinaryMaskStatusRequest($cart->getPaymentDetails());
$payment->execute($statusRequest);

if ($statusRequest->isSuccess()) {
    echo 'We are done';
}

echo "Hmm. We are not. Let's check other possible statuses!";
```

