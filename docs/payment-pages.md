# Customize payment pages

Some gateways show the customer a page of their own during a payment, such as a card form or a
"pay with your wallet" button. Payum renders these pages with Twig. You choose the layout they sit in,
render them with your application's own Twig environment, and replace any single template a gateway
ships.

This page covers the application side. To ship templates in a gateway, see
[Templates](gateways/templates.md).

## How a payment page is rendered

A gateway that needs to show a page returns a "render this template" next action from the command,
naming a template under the gateway's own Twig namespace, such as `@PayumAcme/checkout.html.twig`.
`Payum::capture()` renders that template and returns it as a `200` response, so a capture script needs
no extra code:

```php
<?php
// capture.php

require __DIR__ . '/config.php';

$payum->capture()->send();
```

Every gateway template extends a layout. By default that layout is `@PayumCore/layout.html.twig`, a
complete HTML page with four empty blocks for the gateway to fill:

| Block | What the gateway puts there |
| :--- | :--- |
| `payum_stylesheets` | Stylesheets, inside `<head>` |
| `payum_body` | The page content, such as the form |
| `payum_vendor_javascripts` | Scripts from the PSP, such as its card widget |
| `payum_javascripts` | The gateway's own scripts |

## Render inside your own page

If you print the payment page inside a page of your own, the default layout's `<html>` document is in
the way. Switch to the fragment layout, which renders the same four blocks with nothing around them:

```php
<?php

use Acme\Payum\AcmeConfig;
use Payum\Core\PayumBuilder;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->setLayout('@PayumCore/fragment.html.twig')
    ->getPayum();
```

`setLayout()` applies to every gateway. Call it before `getPayum()`.

## Use your application's Twig environment

To use your own layout, or your own Twig functions, filters and globals in gateway templates, give
Payum your application's Twig environment. Register it under `twig.env` (or `Twig\Environment::class`):

```php
<?php

use Acme\Payum\AcmeConfig;
use Payum\Core\PayumBuilder;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$twig = new Environment(new FilesystemLoader(__DIR__ . '/templates'));

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGlobalService('twig.env', $twig)
    ->setLayout('layouts/payment.html.twig')
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

Payum adds its own namespace and every gateway's namespace to that environment, so gateway templates
keep resolving. Without a registered environment, Payum builds a plain one that knows only those
namespaces.

The layout you pass to `setLayout()` is a template name that your environment can load. A file system
path does not work. Define the four blocks in it, or the gateway's content has nowhere to go:

```twig
{# templates/layouts/payment.html.twig #}
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="/css/shop.css">
        {% block payum_stylesheets "" %}
    </head>
    <body>
        <main class="checkout">
            {% block payum_body "" %}
        </main>
        {% block payum_vendor_javascripts "" %}
        {% block payum_javascripts "" %}
    </body>
</html>
```

If your application has a dependency injection container, you can provide the environment from there
instead. An entry named `twig.env` or `Twig\Environment` in a container passed to `setGlobalContainer()`
works the same way. See [Integrate with a framework](di/framework-integration.md).

## Override a gateway template

To change one template a gateway ships, register a directory of your own under the gateway's Twig
namespace, on your own loader, before you hand the environment to Payum:

```php
<?php

use Acme\Payum\AcmeConfig;
use Payum\Core\PayumBuilder;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader(__DIR__ . '/templates');
$loader->addPath(__DIR__ . '/templates/payum/acme', 'PayumAcme');

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGlobalService('twig.env', new Environment($loader))
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

Save your version under the same file name the gateway uses, such as
`templates/payum/acme/checkout.html.twig`. Twig searches your directory first, so your file wins, and
any template you did not copy still comes from the gateway.

The gateway's documentation names its namespace. You can also read it from the gateway class: it is a key
of the array that `templateNamespaces()` returns.

Your template receives the same variables as the original:

| Variable | What it is |
| :--- | :--- |
| `layout` | The layout to extend, as set with `setLayout()` |
| `gateway` | The gateway's metadata: `name`, `logo`, `websiteUrl` |
| `subject` | The payment or payout, or `null` |
| `token` | The token of this request, or `null`. Post forms back to `token.targetUrl` |
| `command` | The command being handled |
| `context` | The handler context the command ran in |

The gateway may pass more variables of its own. Copy its template as a starting point so you keep them.

## Render a page yourself

If you execute commands yourself rather than through `Payum::capture()`, check the result for a template
to render and render it with `Payum::renderer()`:

```php
<?php

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Result\NextAction\RenderTemplate;

/** @var \Payum\Core\Payum $payum */
/** @var \Payum\Core\Security\TokenInterface $token */

$result = $payum->getGateway('acme')->execute(CaptureCommand::forToken($token));

if ($result->next instanceof RenderTemplate) {
    echo $payum->renderer()->render($result->next->template, $result->next->context);
}
```

The renderer is the same one `Payum::capture()` uses, with your layout and your Twig environment. One
renderer serves every gateway. See [Payment operations](payment-operations.md) for the other next actions
a command can return.
