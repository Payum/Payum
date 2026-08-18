# Instant payment notification

A notification is a callback. A gateway sends it back to you to let you know about changes. It could be due a refund or pending payment acceptance. The diagram shows two examples where notification could be very handy:

![notification](http://www.websequencediagrams.com/cgi-bin/cdraw?lz=cGFydGljaXBhbnQgUGF5cGFsCgAHDGNhcHR1cmUucGhwAAsNbm90aWZ5ABIFCgAZCy0-KwA\_BjogYSBwdXJjYWhzZQoAUgYtPi0AQws6IHBlbmRpbmcAFggtPgBKCjogc3VjY2VzcwBiBmljYXRpb24AMTkARgcAVBZjYW5jZWxlZCAodXNlciB2b2lkIG9uIHAAggcFIHNpZGUp\&s=default)

A refund settling days after the customer left, a bank transfer finally clearing, a dispute the merchant only sees in the PSP's dashboard: none of these happen while anyone is looking at your site. The PSP has to call you back on its own, whenever it has something to say. That call is a notification.

### Making the endpoint reachable

A notification is an HTTP request the PSP's servers make to yours, with nobody's browser involved. It has to reach a URL that is public, answers over plain HTTP or HTTPS, and never redirects or challenges the caller with a login — a PSP retries a failing delivery, it does not follow a redirect or fill in a form. See [notify script](examples/notify-script.md) for the endpoint itself.

### What Payum does with one

`Payum::notify()` verifies the token in the URL, works out which gateway it belongs to, and hands the message to that gateway. A gateway built from handlers answers it with its notify handler; the payment is updated for you, and the PSP gets back whatever the handler decided to answer. You do not parse the request or write the response — call `notify()` and let it run.

### Reacting to a change

Read the status Payum recorded rather than asking the gateway again:

```php
<?php
use Payum\Core\Model\PaymentStatuses;

$payment = $payum->getStorage(Payment::class)->find($identity);

PaymentStatuses::of($payment);   // the status Payum recorded after the last command
```

`PaymentStatuses::of()` returns `null` unless your payment implements `Payum\Core\Model\StatusAwareInterface` — nothing is recorded to read otherwise. Implement it on your own payment class and Payum keeps the status current after every command, so it becomes readable without a gateway and queryable straight from storage — list every payment stuck `Pending`, or every one that reached `Captured` today, without executing anything. See [Keeping the status on the payment](gateways/results.md#keeping-the-status-on-the-payment) for how.

### If a notification never arrives

Networks drop requests and PSPs have outages. When a payment sits in a status that should have moved on, ask the PSP directly instead of waiting for a notification that may not come:

```php
<?php
use Payum\Core\Command\SyncCommand;

$gateway = $payum->getGateway('acme');
$gateway->execute(SyncCommand::forPayment($payment));
```

That is what a reconciliation job does: walk the payments still open, dispatch a `SyncCommand` for each, and let Payum record whatever the PSP answers. See [Commands](gateways/commands.md).

### Writing a gateway that receives them

A gateway receives notifications by shipping a notify handler — see [Webhooks](gateways/webhooks.md).

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
