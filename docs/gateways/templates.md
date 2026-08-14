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

### 2. Declare the templates

Implement `DeclaresTemplates`. What each entry points at decides what it registers: a **directory**
registers a Twig namespace, a **file** registers a template key.

#### Declaring a namespace

```php
<?php
use Payum\Core\Gateway\DeclaresTemplates;
use Payum\Core\Gateway\GatewayInterface;

final class AcmeGateway implements GatewayInterface, DeclaresTemplates
{
    public function templates(): array
    {
        return [
            'PayumAcme' => __DIR__ . '/Resources/views',
        ];
    }

    // …
}
```

A directory registers a Twig namespace, so templates under it can `{% include %}` and `{% import %}`
each other — a partial, a set of macros, another template shipped alongside it. A key alone cannot do
this: it resolves straight to one absolute file, with no namespace for a relative `{% include %}` to
resolve against. Prefer a namespace once a gateway ships more than one template, a partial, or macros.

**Do not name a namespace `PayumCore`.** Core registers its own views — including the layout every
template extends — under that name, and a gateway declaring the same namespace replaces it rather than
adding to it.

#### Declaring a template key

```php
public function templates(): array
{
    return [
        'payum.template.acme.checkout' => __DIR__ . '/Resources/views/checkout.html.twig',
    ];
}
```

A file registers a template key instead. A key is the stable override point: it keeps working when the
gateway later renames or moves the file behind it.

**Write the key out in full.** The convention is `payum.template.{gateway}.{name}`. Two gateways
declaring the same key is a build-time error: `PayumBuilder::getPayum()` throws before either gateway
can be used. Sharing a namespace is not a collision — Twig searches a namespace's directories in the
order they were registered, so two gateways can point the same namespace at their own directory.
Registering the same gateway class twice under different names — a live and a test account, say — is
not a collision either. It is one declaration, used twice.

A declared value that is neither an existing file nor an existing directory is also a build-time error,
naming the gateway.

#### Both together

One `templates()` return can hold both kinds of entry:

```php
public function templates(): array
{
    return [
        'PayumAcme' => __DIR__ . '/Resources/views',
        'payum.template.acme.checkout' => __DIR__ . '/Resources/views/checkout.html.twig',
    ];
}
```

Ship a namespace so a gateway's own templates can include each other, and a key alongside it for the
one template a handler names and an application might want to override.

#### Naming one from a handler

A handler names either form the same way, as `RenderTemplate`'s first argument. The example at the top
of this page names the key; naming a namespaced template instead looks the same:

```php
return CaptureResult::pending(new RenderTemplate('@PayumAcme/checkout.html.twig', [
    'actionUrl' => $context->token()?->getTargetUrl(),
]));
```

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
template at a time instead — the sections below show how.

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

This only takes effect through `addGlobalService()`. A `twig.env` registered on a container supplied
through `PayumBuilder::setGlobalContainer()` is not seen: the environment is resolved from the
container Payum builds itself, which sees `addGlobalService()` entries but not a preset container's.
Register the environment with `addGlobalService()` even in an application that otherwise wires Payum
through `setGlobalContainer()` — see [Framework integration](../di/framework-integration.md).

### Overriding a template

`PayumBuilder::setTemplate()` rebinds a key — or an engine-native name such as
`@PayumAcme/checkout.html.twig` — to a different file. Payum checks its own mapping before falling
through to the engine, so overriding a namespaced name works the same way as overriding a key.
`PayumBuilder::addRenderer()` registers whatever renders that file:

```php
$payum = (new PayumBuilder())
    ->registerGateway('acme', new AcmeConfig(…))
    ->setTemplate('payum.template.acme.checkout', '/app/views/checkout.blade.php')
    ->addRenderer('blade.php', new BladeRenderer(…))
    ->getPayum();
```

`setTemplate('@PayumAcme/checkout.html.twig', '/app/views/checkout.blade.php')` overrides a namespaced
template the same way.

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
