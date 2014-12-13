# Your order integration

In this chapter we would talk about purchase using Order class.
The Order class is defined by you and have any possible methods.
To simply things let's suppose it looks like this:

```php
<?php
namespace App\Model;

class Order
{
    public $details;

    public $price;

    public $currency;
}
```

To allow purchase using this Order we have to create payum's action.
The action is like a driver between your domain and a payment gateway.
As an example we created a capture action that can capture order using foo payment.

```php
<?php
namespace App\Payum\Action;

use App\Model\Order;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;

class CaptureOrderUsingFooAction extends PaymentAwareAction
{
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $order = $request->getModel();

        $request->setModel(new \ArrayObject(array(
            'foo_price' => $order->price,
            'foo_currency' => $order->currency
        )));

        $this->payment->execute($request);

        $order->details = $request->getModel();
        $request->setModel($order);
    }

    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof Order
        ;
    }
}
```

Now we have to add this action to payment object. Also you have to register a storage that able to store Order.
You have to add to `config.php` that was described in [get it started](get-it-started.md) chapter.

```php
<?php
// config.php

use App\Payum\Action\CaptureOrderUsingFooAction;

// ...

$payments['foo']->addAction(new CaptureOrderUsingFooAction);

$storages['App\Model\Order'] = new FilesystemStorage('/path/to/storage', 'App\Model\Order');

// ...
```

```php
<?php
// prepare.php

use App\Model\Order;

include 'config.php';

$storage = $payum->getStorage('App\Model\Order');

$order = $storage->create();
$order = new Order;
$order->price = 1;
$order->currency = 'USD';
$storage->update($order);

$captureToken = $tokenFactory->createCaptureToken('foo', $order, 'done.php');

header("Location: ".$captureToken->getTargetUrl());
```

Back to [index](index.md).