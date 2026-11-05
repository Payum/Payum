# Payum vs Omnipay

[Omnipay](https://omnipay.thephpleague.com/) and Payum are both PHP libraries for taking payments
through many providers. Omnipay gives you a common API for sending requests to a provider. Payum
also handles the parts of a payment around those requests: the customer's trip to the provider and
back, the payment status, and storing payments and tokens. This page explains where they differ, so
you can pick one.

## Your payment model, not the provider's format

With Payum you pass your own payment to a command, and the gateway turns it into whatever its
provider expects. Your checkout code stays the same when you change providers:

```php
<?php

use Payum\Core\Command\CaptureCommand;

$result = $payum->getGateway('acme')->execute(CaptureCommand::forPayment($payment));
```

Providers differ in what they need. One wants card details, another wants return and cancel URLs.
With Omnipay your code supplies those differences. With Payum the gateway asks for what it needs,
and Payum creates the return URLs for you. See [Use your own payment model](your-order-integration.md).

## Every kind of checkout

Some providers redirect the customer, some need a form post, some need their JavaScript or an iframe
on your page, and some need a 3-D Secure check. A Payum gateway tells your application which of these
the customer has to do next, and `$payum->capture()` turns the common ones into a response. Omnipay
covers redirects and card forms, and leaves the rest to you. See
[How Payum works](the-architecture.md#next-actions).

## Statuses

Omnipay tells you whether a response was successful, pending, cancelled or a redirect. Payum has one
set of statuses for every gateway, including authorized, partially captured, refunded, disputed and
charged back, and writes the status onto your payment after each operation. Your code reads the
status from the payment without asking the provider. See [Payment status](payment-status.md).

## Secure payment URLs

You cannot trust an amount or an order id in a URL the customer can edit. Payum sends the customer
to the provider and back with tokens: random, single-use values tied to one payment and one URL. The
same applies to the notify URL a provider calls to confirm a payment. Omnipay leaves this to your
application. See [Tokens and payment URLs](tokens.md).

## Provider data

Payum keeps everything the provider returns on the payment's details, and each result carries the
provider's raw response. When you need the payer's address or the provider's error code, it is
there.

## Sensitive data

Card numbers must not end up in your database by accident. Payum wraps them in a `SensitiveValue`,
which serializes to nothing and masks its value in debug output. See [Sensitive information](working-with-sensitive-information.md).

## Use both

If Payum has no gateway for your provider but Omnipay does, the
[Omnipay bridge](https://github.com/Payum/OmnipayV3Bridge) lets you use the Omnipay gateway through
Payum. See [Supported gateways](supported-gateways.md#omnipay-gateways).

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
