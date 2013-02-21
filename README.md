Authorize.Net AIM
=================

The lib implements [Authorize.Net AIM](http://www.authorize.net/) payment.

## How to capture?

```php
<?php
use Payum\Request\CaptureRequest;
use Payum\AuthorizeNet\Aim\Payment;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;

$authorizeNet = new AuthorizeNetAIM($apiLoginId = 'xxx', $transactionKey = 'xxx');
$authorizeNet->setSandbox(true);

$payment = Payment::create($authorizeNet);

$payment->execute($captureRequest = new CaptureRequest(array(
  'amount' => 10,
  'card_num' => '1234123412341234',
  'exp_date' => '10-02',
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
* [Add](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/configuration.html#obtaining-an-entitymanager) mapping [schema](src/Payum/AuthorizeNet/Aim/Bridge/Doctrine/Resources/mapping/PaymentInstruction.orm.xml) to doctrine configuration. 
* Extend provided [model](src/Payum/AuthorizeNet/Aim/Bridge/Doctrine/Entity/PaymentInstruction.php) and add `id` field.

Want another storage? Contribute!

## Have your cart? Want to use it? No problem!

Write an action:

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

You can star the lib on [github](https://github.com/Payum/AuthorizeNetAim) or [packagist](https://packagist.org/packages/payum/authorize-net-aim). You may also drop a message on Twitter.  

## Need support?

If you are having general issues with [authorize.net](https://github.com/Payum/AuthorizeNetAim) or [payum](https://github.com/Payum/Payum), we suggest posting your issue on [stackoverflow](http://stackoverflow.com/). Feel free to ping @maksim_ka2 on Twitter if you can't find a solution.

If you believe you have found a bug, please report it using the GitHub issue tracker: [authorize.net](https://github.com/Payum/AuthorizeNetAim/issues) or [payum](https://github.com/Payum/Payum/issues), or better yet, fork the library and submit a pull request.

## License

AuthorizeNetAim is released under the MIT License. For more information, see [License](LICENSE).