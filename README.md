Be2Bill
=======

The lib implements [Be2Bill](http://www.be2bill.com/) payment.

## How to capture?

```php
<?php
use Buzz\Client\Curl;

use Payum\Be2Bill\Api;
use Payum\Be2Bill\Payment;
use Payum\Request\CaptureRequest;

$payment = Payment::create(new Api(new Curl(), array(
   'identifier' => 'foo',
   'password' => 'bar',
   'sandbox' => true
))); 

$payment->execute(new CaptureRequest(array(
    'AMOUNT' => '1000', // 10$
    'CLIENTUSERAGENT' => 'Firefox',
    'CLIENTIP' => '82.117.234.33',
    'CLIENTIDENT' => 'anIdent',
    'CLIENTEMAIL' => 'test@example.com',
    'CARDCODE' => '4111111111111111',
    'DESCRIPTION' => 'aDescr',
    'ORDERID' => 'anId',
    'CARDFULLNAME' => 'John Doe',
    'CARDVALIDITYDATE' => '10-13',
    'CARDCVV' => '123'
)));
```

## Was the payment finished successfully?

```php
<?php

//...
use Payum\Request\BinaryMaskStatusRequest;

$statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
$payment->execute($statusRequest)) {

if ($statusRequest->isSuccess()) {
    echo 'We are done';
}

echo "Hmm. We are not. Let's check other possible statuses!";
```

## Have your cart? Want to use it? No problem!

Write an action:

```php
<?php
namespace Foo\Payum\Action;

use Payum\Be2bill\Action\PaymentAwareAction;
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
    
        $rawCaptureRequest = new CaptureRequest(array(
            'AMOUNT' => $cart->getPrice(), // 10$
            'CLIENTUSERAGENT' => $cart->getPayer()->getBrowser(),
            'CLIENTIP' => $cart->getPayer()->getClientIp(),
            'CLIENTIDENT' => $cart->getPayer()->getId(),
            'CLIENTEMAIL' => $cart->getPayer()->getEmail(),
            'DESCRIPTION' => $cart->getDescription(),
            'ORDERID' => $cart->getId(),
            'CARDCODE' => $cart->getPayer()->getCreditCard()->getNumber(),
            'CARDFULLNAME' => $cart->getPayer()->getCreditCard()->getOwnerName(),
            'CARDVALIDITYDATE' => $cart->getPayer()->getCreditCard()->getExpirationDate()->format('y-m'),
            'CARDCVV' => $cart->getPayer()->getCreditCard()->getCvv()
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
            $request->getModel instanceof AwesomeCart
        ;
    }
}
```

Use it:

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