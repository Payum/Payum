# Purchase done action

The done action is the page the customer lands on at the end of a payment, whether it succeeded or not.
It is where you read the payment status and act on it: mark the order paid, send a receipt, or show an
error and offer to try again.

## Name the done route

Pass the route of your done action as the third argument of `prepare()`:

```php
$captureToken = $payum->prepare('offline', $payment, 'app_payment_done');
```

After the gateway has run, the bundle's capture route redirects the customer to `app_payment_done`, with
a token in the URL. Pass route parameters as a fourth argument, for example
`['orderId' => $order->getId()]`.

## Read the payment and its status

Call `done()` with the current request:

```php
<?php
// src/Controller/PaymentController.php

namespace App\Controller;

use Payum\Core\Payum;
use Payum\Core\Result\PaymentStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentController extends AbstractController
{
    #[Route('/payment/done', name: 'app_payment_done')]
    public function done(Request $request, Payum $payum): Response
    {
        $payment = $payum->done($request);

        return match ($payment->getStatus()) {
            PaymentStatus::Captured, PaymentStatus::Authorized => new Response('Thank you for your payment.'),
            PaymentStatus::Pending => new Response('We are waiting for your bank to confirm the payment.'),
            default => new Response('Your payment did not go through.'),
        };
    }
}
```

`done()` verifies the token, invalidates it and returns the payment. A second request with the same URL
throws an exception, so your logic runs once per payment. `PaymentStatus::Pending` means the provider
has not confirmed the payment yet. It may confirm it later through a notification, so do not treat it as
a failure. [Payment status](../payment-status.md) explains each status.

## Gateways registered with `factory`

A gateway registered with `factory` runs 1.x actions, and Payum does not record the status on its
payments. Ask the gateway for the status instead:

```php
use Payum\Core\Request\GetHumanStatus;

$token = $payum->getHttpRequestVerifier()->verify($request);
$gateway = $payum->getGateway($token->getGatewayName());

$gateway->execute($status = new GetHumanStatus($token));
$payment = $status->getFirstModel();

$payum->getHttpRequestVerifier()->invalidate($token);

if ($status->isCaptured()) {
    // ...
}
```

`GetHumanStatus` is deprecated in Payum 2. Switch to `done()` once the gateway is registered by its
config class. See [Register gateways](gateways.md).

## Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
