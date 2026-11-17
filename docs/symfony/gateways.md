# Register gateways

A gateway is Payum's adapter for one payment provider. You register each gateway under a name, and your
code uses that name to take payments with it. This page shows how to register gateways with PayumBundle,
pass them credentials from the environment, and run payments on them from your own code.

## Register a gateway by its config class

Every gateway written for Payum 2 comes with a config class that holds its settings. Name it under
`config`, and pass its constructor arguments under `arguments`:

```yaml
# config/packages/payum.yaml
payum:
    gateways:
        offline:
            config: Payum\Offline\OfflineConfig
            arguments:
                paid: false
```

Each key under `arguments` is the name of a constructor parameter, so the order does not matter. A
misspelled name stops the container from compiling. An entry with `config` takes only `config` and
`arguments`. The bundle rejects any other key, including `factory`.

### Read credentials from the environment

Use Symfony's `%env()%` syntax for API keys and other secrets:

```yaml
# config/packages/payum.yaml
payum:
    gateways:
        acme:
            config: Acme\Payum\AcmeConfig
            arguments:
                apiKey: '%env(ACME_API_KEY)%'
                sandbox: '%env(bool:ACME_SANDBOX)%'
```

The bundle passes the arguments to the config class when Payum builds the gateway, so Symfony reads the
variables at runtime and the values never end up in the compiled container. Use the
[env var processors](https://symfony.com/doc/current/configuration/env_var_processors.html), such as
`bool:` and `int:`, for arguments that are not strings.

### Register one provider more than once

Register the same config class under two names to use two accounts with one provider, for example one
per country:

```yaml
# config/packages/payum.yaml
payum:
    gateways:
        acme_eu:
            config: Acme\Payum\AcmeConfig
            arguments:
                apiKey: '%env(ACME_EU_API_KEY)%'
        acme_us:
            config: Acme\Payum\AcmeConfig
            arguments:
                apiKey: '%env(ACME_US_API_KEY)%'
```

Each gateway gets its own config object. Payum stores the gateway name in every token, so a payment
started with `acme_eu` finishes with `acme_eu`.

## Register a gateway config as a service

When a config needs more than constructor arguments, build it as a service and tag it
`payum.gateway_config`. The `gateway` attribute is the gateway's name:

```yaml
# config/services.yaml
services:
    app.payum.acme_eu:
        class: Acme\Payum\AcmeConfig
        arguments:
            $apiKey: '%env(ACME_EU_API_KEY)%'
        tags:
            - { name: payum.gateway_config, gateway: acme_eu }
```

The service is an ordinary Symfony service, so you can build it with a factory, decorate it or pass it
other services. Tag two services of the same class to register two gateways. A gateway name must be
unique. The container fails to compile when a tag reuses a name from `payum.gateways` or from another tag.

## Gateways without a config class

A gateway that has not moved to Payum 2 yet has no config class. Register it with `factory` and its
options, as in Payum 1:

```yaml
# config/packages/payum.yaml
payum:
    gateways:
        paypal:
            factory: paypal_express_checkout
            username: '%env(PAYPAL_USERNAME)%'
            password: '%env(PAYPAL_PASSWORD)%'
            signature: '%env(PAYPAL_SIGNATURE)%'
            sandbox: true
```

Payum builds these gateways from their 1.x actions and triggers a deprecation notice for each one. They
keep working with the bundle's routes and with `prepare()`. Payum does not record the status on their
payments, so read it with `GetHumanStatus` as
[Purchase done action](purchase-done-action.md#gateways-registered-with-factory) shows. Switch an entry
to `config` once its package ships a config class. [Configuration reference](configuration-reference.md) lists the
`factory` options of the gateways in this repository.

{% hint style="info" %}
Gateways stored in the database with [Configure payment in backend](configure-payment-in-backend.md) are
always built the 1.x way, from a factory name. They cannot use a config class yet.
{% endhint %}

## Run a payment from your code

The bundle's routes cover a payment the customer takes part in: capture, authorize and the done page.
For everything else, such as a refund from your back office, inject `Payum`, get the gateway and execute
a command on it:

```php
<?php
// src/Controller/RefundController.php

namespace App\Controller;

use App\Entity\Payment;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Payum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RefundController extends AbstractController
{
    #[Route('/admin/payments/{id}/refund', name: 'app_refund', methods: ['POST'])]
    public function refund(Payment $payment, Payum $payum): Response
    {
        $result = $payum->getGateway('offline')->execute(RefundCommand::forPayment($payment));

        if ($result->isFailed()) {
            return new Response('The refund failed: '.$result->status->value, Response::HTTP_BAD_GATEWAY);
        }

        return new Response('Refunded.');
    }
}
```

`execute()` returns a result with the new status of the payment. Payum records that status on the
payment and saves it. [Payment operations](../payment-operations.md) shows every command and what to do
with its result.

To run commands on a gateway that has no config class, wrap it in `LegacyGatewayAdapter`. See
[Run a 1.x gateway with commands](../upgrading.md#run-a-1x-gateway-with-commands).

## Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
