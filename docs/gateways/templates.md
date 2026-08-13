# Templates

A handler that needs to show the customer a page — a card form, a wallet button, a "we are checking"
screen — returns a `RenderTemplate` next action naming a template the gateway ships.

```php
use Payum\Core\Result\NextAction\RenderTemplate;

return CaptureResult::pending(new RenderTemplate('@PayumAcme/obtain_token.html.twig', [
    'actionUrl' => $context->token()?->getTargetUrl(),
]));
```

It carries the name and the context, never rendered output, so the application's own renderer and
layout stay in charge.

### Declaring where they live

Implement `DeclaresTemplates` and return namespace => directory:

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

Put the files beside the gateway:

```
src/Payum/Acme/
├── AcmeGateway.php
└── Resources/views/
    └── obtain_token.html.twig
```

A namespace declared here wins over the same namespace supplied by the application. `array_merge` keeps
one directory per namespace, so declaring `PayumCore` **replaces** the namespace rather than shadowing a
single file — a gateway overriding it has to ship everything Twig would otherwise find there, not just
the one file it means to change.

With core's default wiring each gateway's container builds its own Twig `Environment`, so namespaces
stay isolated between gateways. An application that supplies one shared `Environment` to every
gateway — which is what a typical Symfony or Laravel integration does — shares the namespaces too:
`TwigUtil` keeps a single loader per `Environment` in a static map, so two gateways declaring the same
namespace silently resolve through whichever one registered its paths first, and a gateway overriding
`PayumCore` can hijack another gateway's layout. The failure mode is a silently wrong template, not an
error.

### The renderer

`Payum\Core\Template\RendererInterface` is the whole contract:

```php
interface RendererInterface
{
    public function render(string $template, array $context = []): string;
}
```

Core binds it to `Bridge\Twig\TwigRenderer`, which registers the declared paths and makes the
configured layout available to every template as `layout`:

```twig
{% extends layout %}

{% block payum_body %}
    <form action="{{ actionUrl }}" method="post">…</form>
{% endblock %}
```

The layout is `payum.template.layout`, `@PayumCore/layout.html.twig` by default.

Reach the renderer from a gateway when you are acting on a result yourself:

```php
$result = $gateway->execute(CaptureCommand::forToken($token));

if ($result->next instanceof RenderTemplate) {
    echo $gateway->renderer()->render($result->next->template, $result->next->context);
}
```

`Payum::capture()` already does this and returns a Symfony response, so most applications never write
it.

`renderer()` is not declared on `Payum\Core\Gateway\GatewayInterface`, the metadata interface a gateway
implements. What `Payum::getGateway()` returns is `Payum\Core\GatewayInterface`, the executor, which
gets `renderer()` through a `@method` annotation; the method itself is implemented on
`Payum\Core\Gateway`. A third-party class implementing only `Payum\Core\GatewayInterface` would not
have it.

### Another engine

Bind your own implementation against the same id, **per gateway**, from `configureContainer()` — layer
3 in the last-wins ordering described in [Services](services.md):

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
application's `payum.paths`, then this gateway's own `templatePaths()` if it declares any — each
gateway's container composes only what that one gateway contributes, not what every gateway in the
application declares.

`PayumBuilder::addGlobalService(RendererInterface::class, …)` looks like the natural place for a
whole-application default instead. It does not work for a renderer that needs
`payum.template_paths`: everything registered with `addGlobalService()` is rebuilt against the
**global** container (`PayumBuilder::buildSharedDefinitions()` rewrites every id to
`fn () => $globalContainer->get($id)`), and the global container never defines `payum.template_paths` —
that entry exists only on a gateway's own container. A renderer wired with `addGlobalService()` and
asked to resolve `payum.template_paths` throws `DI\NotFoundException: No entry or class found for
'payum.template_paths'`. Bind per gateway instead, or give the renderer its paths some other way that
does not go through the global container.

**A template name is engine-specific.** A gateway naming `@PayumAcme/obtain_token.html.twig` has named
a Twig template; Blade would want `payum-acme::obtain_token`. A renderer may translate names if it
wishes, but core does not, and a gateway shipping templates for more than one engine is the
integration's problem for now. This will be revisited when a real Blade integration exists.

Next: [Services](services.md).
