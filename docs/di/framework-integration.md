# Framework Integration with Dependency Injection

## Overview

Payum v2.0's DI system can integrate with any PSR-11 compatible container, including Symfony, Laravel, and custom frameworks. This guide explains integration patterns.

## Integration Strategy

Payum v2.0 supports two integration approaches:

1. **PayumBuilder with Custom Services** (Recommended for most cases)
   - Use PayumBuilder's built-in PHP-DI container
   - Inject framework services as global services
   - Simple, framework-agnostic

2. **Custom Container Integration** (Advanced)
   - Provide your framework's container to PayumBuilder
   - Full integration with framework DI
   - Requires more setup

## Symfony Integration

### Using PayumBundle (Recommended)

PayumBundle provides native Symfony integration:

```php
# config/packages/payum.yaml
payum:
    security:
        token_storage:
            App\Entity\PaymentToken: { doctrine: orm }

    storages:
        App\Entity\Payment: { doctrine: orm }

    gateways:
        stripe:
            factory: stripe_checkout
            publishable_key: '%env(STRIPE_PUBLISHABLE_KEY)%'
            secret_key: '%env(STRIPE_SECRET_KEY)%'
```

### Using PayumBuilder with Symfony Services

If not using PayumBundle, you can inject Symfony services:

```php
<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Payum;
use Payum\Core\PayumBuilder;
use Psr\Log\LoggerInterface;

class PayumFactory
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $em
    ) {}

    public function createPayum(): Payum
    {
        return (new PayumBuilder())
            // Inject Symfony services as global services
            ->addGlobalService(LoggerInterface::class, $this->logger)
            ->addGlobalService(EntityManagerInterface::class, $this->em)

            // Use Doctrine storage
            ->setTokenStorage(new DoctrineStorage(
                $this->em,
                'App\Entity\PaymentToken'
            ))

            // Configure gateways
            ->addGateway('stripe', [
                'factory' => 'stripe_checkout',
                'publishable_key' => $_ENV['STRIPE_PUBLISHABLE_KEY'],
                'secret_key' => $_ENV['STRIPE_SECRET_KEY'],
            ])

            ->getPayum();
    }
}
```

Register as a service:

```yaml
# config/services.yaml
services:
    App\Service\PayumFactory:
        arguments:
            $logger: '@logger'
            $em: '@doctrine.orm.entity_manager'

    Payum\Core\Payum:
        factory: ['@App\Service\PayumFactory', 'createPayum']
```

### Advanced: Using Symfony Container Directly

Symfony's container is already PSR-11, so it can be handed to `setGlobalContainer()` directly. Your
container is consulted first and Payum's own services fill in the rest, so you only declare what you want to
control — see [Using Your Own Container](customization.md#advanced-using-your-own-container).

Since Symfony service ids are strings rather than class names by default, an adapter is useful to map
Payum's ids onto your services:

```php
<?php

namespace App\DependencyInjection;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;

class PayumContainerAdapter implements ContainerInterface
{
    public function __construct(
        private SymfonyContainerInterface $symfonyContainer
    ) {}

    public function get(string $id)
    {
        return $this->symfonyContainer->get($id);
    }

    public function has(string $id): bool
    {
        return $this->symfonyContainer->has($id);
    }
}

// In your factory
$payum = (new PayumBuilder())
    ->setGlobalContainer(new PayumContainerAdapter($container))
    ->getPayum();
```

Services reached this way are resolvable from a gateway, but they cannot be autowired into the
constructor of an action: Payum only turns the entries of a container into gateway container
definitions when the container can report them. Implement
`Payum\Core\DI\ListableContainerInterface` on the adapter to get that. A Symfony `ServiceLocator`
is a good fit, since it both narrows what Payum can see and lists what it holds:

```php
<?php

namespace App\DependencyInjection;

use Payum\Core\DI\ListableContainerInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

class PayumContainerAdapter implements ListableContainerInterface
{
    public function __construct(
        private ServiceProviderInterface $locator
    ) {}

    public function get(string $id): mixed
    {
        return $this->locator->get($id);
    }

    public function has(string $id): bool
    {
        return $this->locator->has($id);
    }

    public function getKnownEntryNames(): array
    {
        return array_keys($this->locator->getProvidedServices());
    }
}
```

For most applications the `addGlobalService()` approach shown above is simpler and does not require you to
re-declare Payum's own services.

## Laravel Integration

### Using Service Provider

Create a Payum service provider:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Payum\Core\PayumBuilder;
use Payum\Core\Payum;
use Psr\Log\LoggerInterface;

class PayumServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Payum::class, function ($app) {
            return (new PayumBuilder())
                // Inject Laravel services
                ->addGlobalService(
                    LoggerInterface::class,
                    $app->make(LoggerInterface::class)
                )

                // Use Eloquent storage (custom implementation)
                ->setTokenStorage(new EloquentTokenStorage())

                // Configure gateways
                ->addGateway('stripe', [
                    'factory' => 'stripe_checkout',
                    'publishable_key' => config('payum.gateways.stripe.publishable_key'),
                    'secret_key' => config('payum.gateways.stripe.secret_key'),
                ])

                ->getPayum();
        });
    }
}
```

Register in `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\PayumServiceProvider::class,
],
```

### Configuration File

Create `config/payum.php`:

```php
<?php

return [
    'gateways' => [
        'stripe' => [
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
            'secret_key' => env('STRIPE_SECRET_KEY'),
        ],
    ],
];
```

### Using in Controllers

```php
<?php

namespace App\Http\Controllers;

use Payum\Core\Payum;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private Payum $payum
    ) {}

    public function prepare(Request $request)
    {
        $gateway = $this->payum->getGateway('stripe');

        $payment = new \App\Models\Payment();
        $payment->total_amount = $request->input('amount');
        $payment->currency_code = 'USD';
        $payment->description = 'Order #' . $request->input('order_id');

        $this->payum->getStorage('App\Models\Payment')->update($payment);

        $token = $this->payum->getTokenFactory()->createCaptureToken(
            'stripe',
            $payment,
            route('payment.done')
        );

        return redirect($token->getTargetUrl());
    }
}
```

## Plain PHP Integration

For frameworks without native DI:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Payum\Core\PayumBuilder;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create your services
$logger = new Logger('payum');
$logger->pushHandler(new StreamHandler('/var/log/payum.log'));

// Build Payum with custom services
$payum = (new PayumBuilder())
    ->addDefaultStorages()

    // Add custom services
    ->addGlobalService(\Psr\Log\LoggerInterface::class, $logger)

    // Configure gateways
    ->addGateway('stripe', [
        'factory' => 'stripe_checkout',
        'publishable_key' => getenv('STRIPE_PUBLISHABLE_KEY'),
        'secret_key' => getenv('STRIPE_SECRET_KEY'),
    ])

    ->getPayum();

// Use Payum
$gateway = $payum->getGateway('stripe');
```

## Custom Framework Integration

### Step 1: Create Container Adapter (Optional)

If you want full container integration:

```php
<?php

namespace App\Payum;

use Psr\Container\ContainerInterface;

class CustomContainerAdapter implements ContainerInterface
{
    public function __construct(
        private YourFrameworkContainer $container
    ) {}

    public function get(string $id)
    {
        return $this->container->get($id);
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }
}
```

### Step 2: Configure Payum

```php
<?php

$payum = (new PayumBuilder())
    ->setGlobalContainer(new CustomContainerAdapter($yourContainer))
    // ... configure gateways
    ->getPayum();
```

`$yourContainer` only needs the services you want to provide yourself; Payum falls back to its own for
everything else. Declare `payum.security.token_storage`, `TokenFactoryInterface`,
`GenericTokenFactoryInterface` or `HttpRequestVerifierInterface` there to take over any of them.

To have one of your own services injected into a gateway's actions, register it with `addGlobalService()`
as well — Payum cannot discover the ids of a container that does not list its entries.

## Best Practices

### 1. Singleton Registration

Register Payum as a singleton in your container:

```php
// Symfony
services:
    Payum\Core\Payum:
        factory: ['@App\Service\PayumFactory', 'create']
        shared: true

// Laravel
$this->app->singleton(Payum::class, function($app) { ... });
```

### 2. Environment-Specific Configuration

Use environment variables for gateway credentials:

```php
// .env
STRIPE_PUBLISHABLE_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...

// Configuration
->addGateway('stripe', [
    'factory' => 'stripe_checkout',
    'publishable_key' => getenv('STRIPE_PUBLISHABLE_KEY'),
    'secret_key' => getenv('STRIPE_SECRET_KEY'),
])
```

### 3. Storage Configuration

Use framework-native storage:

```php
// Symfony/Doctrine
->setTokenStorage(new DoctrineStorage($em, PaymentToken::class))

// Laravel/Eloquent (custom implementation)
->setTokenStorage(new EloquentTokenStorage())
```

### 4. Logging Integration

Inject framework logger:

```php
->addGlobalService(LoggerInterface::class, $frameworkLogger)
```

### 5. HTTP Client Sharing

Share framework HTTP client:

```php
use Psr\Http\Client\ClientInterface;

->addGlobalService(ClientInterface::class, $frameworkHttpClient)
```

### 6. Clock Sharing

Every time Payum reads goes through the PSR-20 clock in the global container. Register the application's
own clock, so that a test which freezes time freezes it for Payum too:

```php
use Psr\Clock\ClockInterface;

->addGlobalService(ClockInterface::class, $frameworkClock)
```

### 7. Event Dispatcher Sharing

Payum announces the payment lifecycle to the PSR-14 dispatcher in the global container. Register the
application's own, so that its listeners hear about payments alongside everything else:

```php
use Psr\EventDispatcher\EventDispatcherInterface;

->addGlobalService(EventDispatcherInterface::class, $frameworkEventDispatcher)
```

Symfony's `event_dispatcher` is PSR-14 already. A framework whose dispatcher is not needs a small adapter
-- see [Events](../events.md).

## Troubleshooting

### Service Not Found

**Problem:** Container can't find a service

**Solution:** Ensure service is registered in global container:

```php
->addGlobalService(YourService::class, $serviceInstance)
```

### Circular Dependency

**Problem:** Circular dependency detected

**Solution:** Use lazy loading:

```php
->addGlobalService(YourService::class, fn() => new YourService($dep))
```

### Gateway Not Using Custom Service

**Problem:** Gateway doesn't use overridden service

**Solution:** Add service BEFORE configuring gateways:

```php
$payum = (new PayumBuilder())
    ->addGlobalService(ClientInterface::class, $customClient)  // First
    ->addGateway('stripe', [...])  // Then
    ->getPayum();
```

## See Also

- [Dependency Injection overview](README.md)
- [Getting Started](getting-started.md)
- [Customization Guide](customization.md)
- [Migration Guide](migration-guide.md)
- [Symfony PayumBundle documentation](../symfony/get-it-started.md)
- [Laravel package documentation](../laravel/get-it-started.md)
- [Frameworks and e-commerce integration](../frameworks-and-e-commerce-integration.md)

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
