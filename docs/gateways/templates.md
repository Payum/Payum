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

A namespace declared here wins over the same namespace supplied by the application, so a gateway can
override a template core ships. Each gateway gets its own container and its own Twig environment, so
two gateways cannot collide.

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

### Another engine

Bind your own implementation against the same id. Whole-application defaults go on the builder:

```php
$payum = (new PayumBuilder())
    ->addGlobalService(RendererInterface::class, new BladeRenderer($factory))
    ->registerGateway('acme', new AcmeConfig(…))
    ->getPayum();
```

The paths a renderer should resolve against are in the container as `payum.template_paths` — the
application's `payum.paths` composed with every gateway's `templatePaths()`.

**A template name is engine-specific.** A gateway naming `@PayumAcme/obtain_token.html.twig` has named
a Twig template; Blade would want `payum-acme::obtain_token`. A renderer may translate names if it
wishes, but core does not, and a gateway shipping templates for more than one engine is the
integration's problem for now. This will be revisited when a real Blade integration exists.

Next: [Services](services.md).
