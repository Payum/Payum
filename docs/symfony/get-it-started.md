# Get started

PayumBundle adds Payum to a Symfony application. It registers the `Payum` service, the routes that run
a payment, and the storages and gateways you configure in `config/packages/payum.yaml`. This page takes
you through your first payment with the Offline gateway, which marks a payment as paid without talking
to a payment provider, so you can follow it without an account anywhere.

If you are upgrading an application that already uses the bundle, read
[Upgrade to Payum 2](upgrading.md) instead.

## Install the bundle

Install the bundle, Payum, a gateway and an HTTP client with [Composer](https://getcomposer.org/):

```bash
composer require payum/payum-bundle payum/core:^2.0 payum/offline:^2.0 php-http/guzzle7-adapter
```

`payum/offline` is the gateway, Payum's adapter for one payment provider. Replace it with the package
of your provider. [Supported gateways](../supported-gateways.md) lists them. Gateways talk to their
provider through a PSR-18 HTTP client, and `php-http/guzzle7-adapter` is one. Any
[client implementation](https://packagist.org/providers/php-http/client-implementation) works.

Symfony Flex enables the bundle for you. Without Flex, add it to `config/bundles.php`:

```php
<?php
// config/bundles.php

return [
    // ...
    Payum\Bundle\PayumBundle\PayumBundle::class => ['all' => true],
];
```

Then import the bundle's routes. They are the URLs the customer passes through while a payment runs:

```yaml
# config/routes/payum.yaml
payum_all:
    resource: "@PayumBundle/Resources/config/routing/all.yaml"
```

## Create the entities

Payum stores two things for you: the payment, and the tokens that protect the payment URLs. A token is
a random single-use value in the URL that tells Payum which payment and gateway a request is for.
Create an entity for each:

```php
<?php
// src/Entity/Payment.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Payment as BasePayment;

#[ORM\Entity]
#[ORM\Table(name: 'payment')]
class Payment extends BasePayment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;
}
```

```php
<?php
// src/Entity/PaymentToken.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Token;

#[ORM\Entity]
#[ORM\Table(name: 'payment_token')]
class PaymentToken extends Token
{
}
```

The bundle maps the fields of `Payment` and `Token` for you, including the payment's `status` column, so
each entity only adds its table and, for the payment, an id. The token uses its hash as its id. Create
the tables with a migration or with `bin/console doctrine:schema:update --force`.
[Storages](storages.md) shows the other storages the bundle supports.

## Configure Payum

Register the storages and the gateway:

```yaml
# config/packages/payum.yaml
payum:
    security:
        token_storage:
            App\Entity\PaymentToken: { doctrine: orm }

    storages:
        App\Entity\Payment: { doctrine: orm }

    gateways:
        offline:
            config: Payum\Offline\OfflineConfig
```

`offline` is the name you use for the gateway in your code. `config` is the gateway's config class,
which holds its settings and credentials. [Register gateways](gateways.md) shows how to pass those
settings, including secrets from environment variables.

## Create the payment

Create a controller that fills in a payment and sends the customer to the gateway:

```php
<?php
// src/Controller/PaymentController.php

namespace App\Controller;

use App\Entity\Payment;
use Payum\Core\Payum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class PaymentController extends AbstractController
{
    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(Payum $payum): RedirectResponse
    {
        $payment = new Payment();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(1000);
        $payment->setDescription('Order 1001');
        $payment->setClientId('customer-42');
        $payment->setClientEmail('customer@example.com');

        $captureToken = $payum->prepare('offline', $payment, 'app_payment_done');

        return $this->redirect($captureToken->getTargetUrl());
    }
}
```

Amounts are in the currency's smallest unit, so `1000` with `'EUR'` is 10.00 EUR. `prepare()` saves the
payment and returns a capture token. Capture means taking the customer's money. The token's URL points
at the bundle's capture route, which runs the gateway. The third argument is the route the customer
lands on afterwards, paid or not. Always pass it, because the bundle has no default.

## Show the result

Add the route you passed to `prepare()` to `PaymentController`, with `use` statements for
`Payum\Core\Result\PaymentStatus`, `Symfony\Component\HttpFoundation\Request` and
`Symfony\Component\HttpFoundation\Response`. It reads the payment and its status:

```php
#[Route('/payment/done', name: 'app_payment_done')]
public function done(Request $request, Payum $payum): Response
{
    $payment = $payum->done($request);

    if (PaymentStatus::Captured === $payment->getStatus()) {
        return new Response('Thank you, your payment of '.$payment->getTotalAmount().' '.$payment->getCurrencyCode().' went through.');
    }

    return new Response('Your payment did not go through: '.$payment->getStatus()->value);
}
```

`done()` checks the token, deletes it and returns the payment. Because the token is gone, reloading the
page throws an exception instead of running your logic twice. This is where you mark the order paid,
send a receipt or show an error. `PaymentStatus::Pending` means the provider has not confirmed the
payment yet. [Payment status](../payment-status.md) explains each status.

Open `/checkout` in your browser. The Offline gateway captures the payment straight away, so you land on
the done page with a captured payment.

## Next steps

- [Register gateways](gateways.md) covers credentials, several accounts with one provider, and gateways
  that have not moved to Payum 2 yet.
- To refund, cancel or authorize a payment, inject `Payum` and run a command on the gateway.
  [Payment operations](../payment-operations.md) shows each command.
- Providers that confirm payments from their own servers call a notify URL. The bundle's routes include
  it. See [Payment notifications](../instant-payment-notification.md).
- [Use your services in Payum](services.md) shows how gateways get your services and how to listen to
  Payum's events.

## Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
