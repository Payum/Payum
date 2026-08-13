# Templates

When a handler needs to show the customer a page — a card form, a wallet button, a "we are checking"
screen — it returns a `RenderTemplate` next action naming a template your gateway ships.

```php
use Payum\Core\Result\NextAction\RenderTemplate;

return CaptureResult::pending(new RenderTemplate('@PayumAcme/obtain_token.html.twig', [
    'actionUrl' => $context->token()?->getTargetUrl(),
]));
```

It carries the name and the context, never rendered output, so the application's own renderer and
layout stay in charge.

Three steps get you there.

### 1. Ship the template files

Put them beside the gateway:

```
src/Payum/Acme/
├── AcmeGateway.php
└── Resources/views/
    └── obtain_token.html.twig
```

### 2. Tell Payum where they are

Implement `DeclaresTemplates` and point a namespace at that directory:

```php
<?php
use Payum\Core\Gateway\DeclaresTemplates;
use Payum\Core\Gateway\GatewayInterface;

final class AcmeGateway implements GatewayInterface, DeclaresTemplates
{
    public function templatePaths(): array
    {
        return ['PayumAcme' => __DIR__ . '/Resources/views'];
    }

    // …
}
```

The namespace is how handlers name the templates: `@PayumAcme/obtain_token.html.twig`.

**Name it after your gateway** — `Payum` plus the gateway name is the convention. Two rules keep you
out of trouble:

* **Never use `PayumCore`.** Declaring it replaces core's own templates instead of adding to them,
  including the layout every Payum template extends. You would have to ship replacements for all of
  them.
* **Pick something distinctive.** An application can configure Payum so that every gateway shares one
  Twig environment. Two gateways declaring the same namespace then collide, and one silently renders
  the other's templates. A namespace nobody else would choose avoids it.

### 3. Write the template

The layout is available to every template as `layout`:

```twig
{% extends layout %}

{% block payum_body %}
    <form action="{{ actionUrl }}" method="post">…</form>
{% endblock %}
```

Extend it rather than writing a whole HTML document, so your page picks up whatever the application
has configured. It defaults to `@PayumCore/layout.html.twig`; an application changes it with the
`payum.template.layout` setting.

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

`renderer()` lives on `Payum\Core\Gateway`, which is what `$payum->getGateway()` hands you.

### Using another templating engine

Everything above assumes Twig, which is what core ships and what most gateways want. This section is
for an integration replacing it — a Laravel package rendering with Blade, say. Skip it if you are
writing a gateway.

`Payum\Core\Template\RendererInterface` is the whole contract:

```php
interface RendererInterface
{
    public function render(string $template, array $context = []): string;
}
```

Bind your own implementation against the same id, **per gateway**, from `configureContainer()` — see
the last-wins ordering in [Services](services.md):

```php
use Payum\Core\DI\ContainerConfiguration;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Template\RendererInterface;
use Psr\Container\ContainerInterface;

final class AcmeGateway implements GatewayInterface, ContainerConfiguration
{
    public function configureContainer(): array
    {
        return [
            RendererInterface::class => static fn (ContainerInterface $c): RendererInterface => new BladeRenderer(
                $c->get('payum.template_paths'),
            ),
        ];
    }
}
```

`payum.template_paths` is what a renderer resolves against: core's own `PayumCore` default, then the
application's `payum.paths`, then this gateway's `templatePaths()`. It exists only on a gateway's own
container.

That last point matters, because `PayumBuilder::addGlobalService(RendererInterface::class, …)` looks
like the natural place for an application-wide default and does not work here. A globally registered
service is built by the application-wide container, which has no `payum.template_paths`, so a renderer
wired that way throws when it asks for one. Bind per gateway, or give your renderer its paths some
other way.

**A template name is engine-specific.** A gateway naming `@PayumAcme/obtain_token.html.twig` has named
a Twig template; Blade would want `payum-acme::obtain_token`. A renderer may translate names if it
wishes, but core does not, and a gateway shipping templates for more than one engine is the
integration's problem for now. This will be revisited when a real Blade integration exists.

Next: [Services](services.md).
