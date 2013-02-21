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
$payment->execute($statusRequest);
if ($statusRequest->isSuccess()) {
    echo 'We are done';
}

echo "Hmm. We are not. Let's check other possible statuses!";
```

## Want to store payments somewhere?

There are two storage supported out of the box. [doctrine2](https://github.com/Payum/Payum/blob/master/src/Payum/Bridge/Doctrine/Storage/DoctrineStorage.php)([offsite](http://www.doctrine-project.org/)) and [filesystem](https://github.com/Payum/Payum/blob/master/src/Payum/Storage/FilesystemStorage.php).
The filesystem storage is easy to setup, does not have any requirements. It is expected to be used more in tests. 
To use doctrine2 storage you have to follow several steps:

* [Install](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/installation.html) doctrine2 lib. 
* [Add](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/configuration.html#obtaining-an-entitymanager) mapping [schema](src/Payum/Be2Bill/Bridge/Doctrine/Resources/mapping/PaymentInstruction.orm.xml) to doctrine configuration. 
* Extend provided [model](src/Payum/Be2Bill/Bridge/Doctrine/Entity/PaymentInstruction.php) and add `id` field.

Want another storage? Contribute!

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
            $request->getModel() instanceof AwesomeCart
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

## Like it? Spread the world!

You can star the lib on [github](https://github.com/Payum/Be2Bill) or [packagist](https://packagist.org/packages/payum/be2bill). You may also drop a message on Twitter.  

## Need support?

If you are having general issues with [be2bill](https://github.com/Payum/Be2Bill) or [payum](https://github.com/Payum/Payum), we suggest posting your issue on [stackoverflow](http://stackoverflow.com/). Feel free to ping @maksim_ka2 on Twitter if you can't find a solution.

If you believe you have found a bug, please report it using the GitHub issue tracker: [be2bill](https://github.com/Payum/Be2Bill/issues) or [payum](https://github.com/Payum/Payum/issues), or better yet, fork the library and submit a pull request.

## License

Be2bill is released under the MIT License. For more information, see [License](LICENSE).