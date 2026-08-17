# Templates

When a handler needs to show the customer a page — a card form, a wallet button, a "we are checking"
screen — it returns a `RenderTemplate` next action naming a Twig template your gateway ships.

```php
use Payum\Core\Result\NextAction\RenderTemplate;

return CaptureResult::pending(new RenderTemplate('@PayumAcme/checkout.html.twig', [
    'actionUrl' => $context->token()?->getTargetUrl(),
]));
```

It carries the template name and the context, never rendered output, so the application's own layout
stays in charge.

Three steps get you there.

### 1. Ship the template files

Put them beside the gateway:

```
src/Payum/Acme/
├── AcmeGateway.php
└── Resources/views/
    └── checkout.html.twig
```

### 2. Declare the namespace

Implement `DeclaresTemplates`:

```php
<?php
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

    // …
}
```

Each entry registers a Twig namespace, so templates under that directory can `{% include %}` and
`{% import %}` each other — a partial, a set of macros, another template shipped alongside it.

**Do not name a namespace `PayumCore`.** Core registers its own views — including the layout every
template extends — under that name, and declaring it yourself is a build-time error. So is pointing a
namespace at a directory that does not exist.

Two gateways sharing a namespace is not an error: Twig searches a namespace's directories in the order
they were registered. Registering the same gateway class twice under different names — a live and a
test account, say — is not a collision either. It is one declaration, used twice.

### 3. Write the template

The layout is available to every template as `layout`:

```twig
{% extends layout %}

{% block payum_body %}
    <form action="{{ actionUrl }}" method="post">…</form>
{% endblock %}
```

Extend it rather than writing a whole HTML document, so your page picks up whatever the application
has configured. It defaults to `@PayumCore/layout.html.twig`; an application changes it with
`PayumBuilder::setLayout()` — see below.

### What every template is given

Alongside whatever your handler passes, Payum adds these on its own, in
`TemplateRenderMiddleware` — see [Middleware](middleware.md). You do not have to thread them through the
`RenderTemplate` yourself.

| Variable | What it is |
| :--- | :--- |
| `layout` | The layout to extend |
| `gateway` | The gateway being processed — name, logo, website |
| `subject` | What is being paid: a payment, or a payout |
| `token` | The token this execution belongs to, when there is one |
| `command` | The command being handled |

**`gateway`** is your gateway's own metadata, so a template can label itself without hard-coding a name:

```twig
<h1>Pay with {{ gateway.name }}</h1>
<img src="{{ gateway.logo.value }}" alt="{{ gateway.name }}">
```

**`subject`** is the payment for a payment command, which is where the amount and currency live:

```twig
<p>{{ subject.totalAmount }} {{ subject.currencyCode }}</p>
<p>{{ subject.description }}</p>
```

**`token`** is what you post a form back to. `targetUrl` returns the customer to this same command, which
is how a two-step capture resumes; `afterUrl` is where the payment finishes:

```twig
<form action="{{ token.targetUrl }}" method="post">
    <button type="submit">Pay</button>
</form>
```

`subject` and `token` can both be null — a payout has no payment, and a command dispatched without a
token has no token. Guard them if your template runs in either case:

```twig
{% if token %}
    <form action="{{ token.targetUrl }}" method="post">…</form>
{% endif %}
```

Anything your handler passes wins, so you can override any of these by naming them yourself:

```php
return CaptureResult::pending(new RenderTemplate('@PayumAcme/checkout.html.twig', [
    'subject' => $somethingElse,
]));
```

### That is all a gateway has to do

`Payum::capture()` renders the template and returns the response, so a gateway never writes rendering
code:

```php
$response = $payum->capture($request); // 200, carrying your rendered template
```

An application that drives commands itself reaches the renderer from the gateway:

```php
$result = $gateway->execute(CaptureCommand::forToken($token));

if ($result->next instanceof RenderTemplate) {
    echo $gateway->renderer()->render($result->next->template, $result->next->context);
}
```

`renderer()` lives on `Payum\Core\Gateway`, which is what `$payum->getGateway()` hands you. It renders
the template the same way `Payum::capture()` does.

### The renderer belongs to the application

One Twig renderer, shared by every gateway, renders every namespaced template. A gateway declaring
`Payum\Core\Template\RendererInterface` from `configureContainer()` is a build-time error: replacing the
shared renderer would break every other gateway's templates, including the layout every one of them
extends. An application overrides one template at a time instead — the sections below show how.

Everything from here on is for an application embedding Payum, not for a gateway shipping one. Skip it
if you are writing a gateway.

### Supplying your own Twig environment

By default, Payum renders with a Twig `Environment` it builds itself. An application supplies its own
instead by registering it under `twig.env` (or `Twig\Environment::class`) with
`PayumBuilder::addGlobalService()`:

```php
$payum = (new PayumBuilder())
    ->addGlobalService('twig.env', $container->get('twig'))
    ->setLayout('@App/payum.html.twig')
    ->getPayum();
```

Gateway templates then render with the application's own functions, filters, globals and extensions —
which is what a custom layout usually needs. Payum still registers core's and every gateway's
namespaces onto that environment, so gateway templates keep resolving.

### Overriding a template

An application overrides a gateway's template by registering its own directory under the gateway's
namespace, on its own Twig environment, before handing that environment to Payum:

```php
$twig = $container->get('twig');
$twig->getLoader()->addPath('/app/resources/views/acme', 'PayumAcme');

$payum = (new PayumBuilder())
    ->addGlobalService('twig.env', $twig)
    ->registerGateway('acme', new AcmeConfig(…))
    ->getPayum();
```

A namespace's directories are searched in the order they were registered, and Payum adds the gateway's
own directory after resolving your environment. A directory you registered first is searched first: a
file you provide there wins, and anything you did not provide falls through to the gateway's own
directory under the same namespace.

### Choosing a layout

An application embedding Payum's output in its own page, rather than serving `Payum::capture()`'s
response as a full page, points every template at a different layout:

```php
$payum = (new PayumBuilder())
    ->registerGateway('acme', new AcmeConfig(…))
    ->setLayout('@PayumCore/fragment.html.twig')
    ->getPayum();
```

`@PayumCore/fragment.html.twig` renders the same blocks with no surrounding `<html>` document, ready to
sit inside a page of your own. Point `setLayout()` at an absolute path to your own Twig layout instead,
to reuse your application's page around every gateway's output — it resolves the same way a gateway's
own templates do. The default is `@PayumCore/layout.html.twig`, a complete page.

Next: [Services](services.md).
