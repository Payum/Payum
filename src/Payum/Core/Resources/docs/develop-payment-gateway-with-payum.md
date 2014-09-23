# Develop payment gateway with payum

This chapter could be useful for a developer who wants to create a gateway on top of payum.
Here we would briefly describe general approaches, and things you should start from.
Let's assume you want to implement the most common task: purchase a product and get payment status.
For this you would send a request to a payment gateway using username and password provided.

_**Note**: Before you start we would suggest to read [the architecture](the-architecture.md) chapter._

## Capture action.

Purchasing will be done by `CaptureAction`. This action will contain all payment related logic.
We assume you use `ArrayObject` as model but you of course may change it to what ever you want.

```php
<?php
namespace App\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\Capture;

class CaptureAction implements ActionInterface
{
    protected $gatewayUsername;

    protected $gatewayPassword;

    public function __construct($gatewayUsername, $gatewayPassword)
    {
        $this->gatewayUsername = $gatewayUsername;
        $this->gatewayPassword = $gatewayPassword;
    }

    public function execute($request)
    {
        $model = $request->getModel();

        if (isset($model['amount']) && isset($model['currency'])) {

            //do purchase call to the payment gateway using username and password.

            $model['status'] = 'success';
        } else {
            $model['status'] = 'error';
        }
    }

    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
```

## Status action.

`StatusAction` would contain all the rules of payment status calculation.
The action must make its decisions using the model you pass with the request.
Let's assume your model have `status` field and it can be either success or error.

```php
<?php
namespace App\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    public function execute($request)
    {
        $model = $request->getModel();

        if (false == isset($model['status'])) {
            $request->markNew();

            return;
        }

        if ('success' == $model['status']) {
            $request->markCaptured();

            return;
        }

        if ('error' == $model['status']) {
            $request->markFailed();

            return;
        }

        $request->markUnknown();
    }

    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
```

# Usage

Now you want knit all things together and start use it. Okay,
To make it work we have to create a payment object and put all we did into it.

```php
<?php
namespace App;

App\Payum\Action\CaptureAction;
App\Payum\Action\StatusAction;
use Payum\Core\Payment;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;

$payment = new Payment;
$payment->addAction(new CaptureAction('aUsername', 'aPassword'));
$payment->addAction(new StatusAction);

$model = new ArrayObject(array(
    'amount' => 10,
    'currency' => 'USD',
));

$payment->execute(new Capture($model));
$payment->execute($status = new GetHumanStatus($model));

if ($status->isCaptured()) {
    echo 'We purchase staff successfully';
} else if ($status->isFailed()) {
    echo 'An error occurred';
} else {
    echo 'Something went wrong but we don`t know the exact status';
}
```

Enjoy!

Back to [index](index.md).
