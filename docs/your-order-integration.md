# Your order integration

In this chapter we would talk about purchase using Payment class.
The Payment class is defined by you and have any possible methods.
To simply things let's suppose it looks like this:

```php
<?php
namespace App\Model;

class Payment
{
    public $details;

    public $price;

    public $currency;
}
```

To allow purchase using this Payment we have to create payum's action.
The action is like a driver between your domain and a gateway.
As an example we created a capture action that can capture order using foo gateway.

```php
<?php
namespace App\Payum\Action;

use App\Model\Payment;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\GatewayAwareInterface;

class CaptureOrderUsingFooAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait
    
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $order = $request->getModel();

        $request->setModel(new \ArrayObject(array(
            'foo_price' => $order->price,
            'foo_currency' => $order->currency
        )));

        $this->gateway->execute($request);

        $order->details = $request->getModel();
        $request->setModel($order);
    }

    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof Payment
        ;
    }
}
```

Now we have to add this action to gateway object. Also you have to register a storage that able to store Payment.
You have to add to `config.php` that was described in [get it started](get-it-started.md) chapter.

```php
<?php
// config.php

use App\Payum\Action\CaptureOrderUsingFooAction;

// ...

$gateways['foo']->addAction(new CaptureOrderUsingFooAction);

$storages['App\Model\Payment'] = new FilesystemStorage('/path/to/storage', 'App\Model\Payment');

// ...
```

```php
<?php
// prepare.php

use App\Model\Payment;

include __DIR__.'/config.php';

/** @var \Payum\Core\Payum $payum */
$storage = $payum->getStorage('App\Model\Payment');

$order = $storage->create();
$order = new Payment;
$order->price = 1;
$order->currency = 'USD';
$storage->update($order);

$captureToken = $payum->getTokenFactory()->createCaptureToken('foo', $order, 'done.php');

header("Location: ".$captureToken->getTargetUrl());
```

Back to [index](index.md).