# Templates

Some gateways show the customer a page instead of redirecting them: a card form built with the PSP's
JavaScript, a wallet button, a "we are checking your payment" screen. A handler asks for one by
returning a `RenderTemplate` next action that names a Twig template the gateway ships:

```php
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NextAction\RenderTemplate;

return CaptureResult::pending(new RenderTemplate('@PayumAcme/checkout.html.twig', [
    'publishableKey' => $this->api->publishableKey(),
]));
```

The result carries the template's name and variables, never rendered HTML, so the application's own
renderer and layout decide how it looks. This page covers the gateway's side. How an application sets
the layout, uses its own Twig environment or replaces one of your templates is in
[Customize payment pages](../payment-pages.md).

## Ship the template files

Put the templates in the package, beside the gateway class:

```
src/
├── AcmeGateway.php
└── Resources/
    └── views/
        ├── checkout.html.twig
        └── _card_fields.html.twig
```

## Declare the namespace

Implement `Payum\Core\Gateway\DeclaresTemplates` and map a Twig namespace to the directory:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum;

use Payum\Core\Gateway\DeclaresTemplates;
use Payum\Core\Gateway\GatewayInterface;

final class AcmeGateway implements GatewayInterface, DeclaresTemplates
{
    public function templateNamespaces(): array
    {
        return [
            'PayumAcme' => __DIR__ . '/Resources/views',
        ];
    }

    // name(), logo(), websiteUrl(), configClass() and handlers() as before
}
```

Handlers then name templates as `@PayumAcme/checkout.html.twig`, and templates in that directory can
`{% include %}` and `{% import %}` each other through the same namespace.

{% hint style="warning" %}
Never name your namespace `PayumCore`. Payum's own views, including the layout every template extends,
live there, and declaring it makes `getPayum()` throw. So does a namespace that points at a directory
that does not exist.
{% endhint %}

Name the namespace after your gateway. Two gateways that declare the same namespace do not fail:
Twig searches the directories in the order they were registered, so the first gateway's file wins.
Registering your gateway twice, for two accounts, declares the namespace once.

## Write the template

Extend the layout Payum passes to every template as `layout`, and put your markup in the `payum_body`
block:

```twig
{% extends layout %}

{% block payum_body %}
    <h1>Pay with {{ gateway.name }}</h1>

    <form action="{{ token.targetUrl }}" method="post">
        {% include '@PayumAcme/_card_fields.html.twig' %}
        <button type="submit">Pay</button>
    </form>
{% endblock %}

{% block payum_javascripts %}
    <script src="https://js.acme.test/v1/" data-key="{{ publishableKey }}"></script>
{% endblock %}
```

Extend `layout` rather than writing a whole HTML document, so your page fits whatever page the
application wraps around it. The default layout has four blocks: `payum_stylesheets` in the head, then
`payum_body`, `payum_vendor_javascripts` and `payum_javascripts` in the body.

## What every template is given

Payum adds these variables to every `RenderTemplate`, so a handler does not pass them itself:

| Variable | What it is |
| :--- | :--- |
| `layout` | The layout to extend |
| `gateway` | The gateway class instance, for `gateway.name` and `gateway.logo.value` |
| `subject` | What is being paid: the payment, or a payout. Can be null |
| `token` | The token this execution arrived on. Null when the command came without one |
| `command` | The command being handled |
| `context` | The handler's `Context` |

`subject` is where the amount and description live:

```twig
<p>{{ subject.description }}: {{ subject.totalAmount }} {{ subject.currencyCode }}</p>
```

`token.targetUrl` sends the customer back to the same command, which is how a form posts to the
capture's second run. `token.afterUrl` is where the customer goes once the payment is finished.

Guard `subject` and `token` if the template can run without them:

```twig
{% if token %}
    <form action="{{ token.targetUrl }}" method="post">...</form>
{% endif %}
```

A variable the handler passes under the same name wins over the one Payum adds.

## Read what the form posted

When the customer submits the form, the capture runs again on the token's URL. Read the posted fields
from the HTTP request on the context:

```php
public function handle(CaptureCommand $command, Context $context): CaptureResult
{
    $posted = (array) $context->httpRequest()->getParsedBody();

    if (isset($posted['acme_payment_method'])) {
        $charge = $this->api->charge($posted['acme_payment_method'], $context->amount());

        return CaptureResult::captured($charge['id']);
    }

    return CaptureResult::pending(new RenderTemplate('@PayumAcme/checkout.html.twig', [
        'publishableKey' => $this->api->publishableKey(),
    ]));
}
```

## Do not replace the renderer

One renderer, `Payum\Core\Template\RendererInterface`, renders every gateway's templates, and it belongs
to the application. A gateway that declares `RendererInterface` in `configureContainer()` makes
`getPayum()` throw, because replacing it would break every other gateway's templates.

An application that wants your page to look different overrides your template in its own Twig
environment. See [Customize payment pages](../payment-pages.md).

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
