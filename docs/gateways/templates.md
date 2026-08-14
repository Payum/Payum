# Templates

When a handler needs to show the customer a page — a card form, a wallet button, a "we are checking"
screen — it returns a `RenderTemplate` next action naming a template key your gateway ships.

```php
use Payum\Core\Result\NextAction\RenderTemplate;

return CaptureResult::pending(new RenderTemplate('payum.template.acme.checkout', [
    'actionUrl' => $context->token()?->getTargetUrl(),
]));
```

It carries the key and the context, never a path or rendered output, so the application's own renderer
and layout stay in charge. Payum resolves the key to a file at render time.

Three steps get you there.

### 1. Ship the template files

Put them beside the gateway:

```
src/Payum/Acme/
├── AcmeGateway.php
└── Resources/views/
    └── checkout.html.twig
```

### 2. Declare the keys

Implement `DeclaresTemplates` and map each key to the absolute path of its file:

```php
<?php
use Payum\Core\Gateway\DeclaresTemplates;
use Payum\Core\Gateway\GatewayInterface;

final class AcmeGateway implements GatewayInterface, DeclaresTemplates
{
    public function templates(): array
    {
        return [
            'payum.template.acme.checkout' => __DIR__ . '/Resources/views/checkout.html.twig',
        ];
    }

    // …
}
```

A handler only ever names the key — `payum.template.acme.checkout` — never the file behind it.

**Write the key out in full.** The convention is `payum.template.{gateway}.{name}`. Two gateways
declaring the same key is a build-time error: `PayumBuilder::getPayum()` throws before either gateway
can be used. Registering the same gateway class twice under different names — a live and a test
account, say — is not a collision. It is one declaration, used twice.

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

`renderer()` lives on `Payum\Core\Gateway`, which is what `$payum->getGateway()` hands you. It resolves
the key the same way `Payum::capture()` does.

### The renderer belongs to the application

One renderer, shared by every gateway, resolves every key — Twig, unless the application registers
something else. A gateway declaring `Payum\Core\Template\RendererInterface` from
`configureContainer()` is a build-time error: replacing the shared renderer would break every other
gateway's templates, including the layout every one of them extends. An application overrides one
template at a time instead — the next section shows how.

### Overriding a template

Everything from here on is for an application embedding Payum, not for a gateway shipping one. Skip it
if you are writing a gateway.

`PayumBuilder::setTemplate()` rebinds a key to a different file. `PayumBuilder::addRenderer()`
registers whatever renders that file:

```php
$payum = (new PayumBuilder())
    ->registerGateway('acme', new AcmeConfig(…))
    ->setTemplate('payum.template.acme.checkout', '/app/views/checkout.blade.php')
    ->addRenderer('blade.php', new BladeRenderer(…))
    ->getPayum();
```

A renderer is the whole contract:

```php
interface RendererInterface
{
    public function render(string $template, array $context = []): string;
}
```

Extensions are keyed **without the leading dot**, and the longest match wins, so `blade.php` beats
`php` — a `.blade.php` file reaches `BladeRenderer` even with a plain `.php` renderer also registered.
Twig is registered under `twig` already, so a gateway shipping ordinary `.html.twig` templates needs
none of this.

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
