### How to manage authorize redirect?

```php
<?php
//Source: Payum\Examples\ReadmeTest::interactiveRequests()
use Payum\Examples\Request\AuthorizeRequest;
use Payum\Examples\Action\AuthorizeAction;
use Payum\Request\CaptureRequest;
use Payum\Request\RedirectUrlInteractiveRequest;
use Payum\Payment;

$payment = new Payment;
$payment->addAction(new AuthorizeAction());

$request = new AuthorizeRequest($model);

if ($interactiveRequest = $payment->execute($request, $catchInteractive = true)) {
    if ($interactiveRequest instanceof RedirectUrlInteractiveRequest) {
        echo 'User must be redirected to '.$interactiveRequest->getUrl();
    }

    throw $interactiveRequest;
}
```