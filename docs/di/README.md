# Dependency Injection

Starting with Payum 2.0 the library is wired together by a dependency injection container powered by [PHP-DI](https://php-di.org/). Gateways, actions, extensions and the services they depend on are declared as container definitions instead of being assembled by hand, and shared services such as the PSR-18 HTTP client or the token storage are created once and reused by every gateway.

This section explains how the container is put together, how to customize it, and how to plug it into your framework.

### Guides

* [Getting started](getting-started.md) — the container architecture, `PayumBuilder`, global services and how to write a gateway factory.
* [Customization](customization.md) — override default services, register your own, and hand Payum a container of your own.
* [Framework integration](framework-integration.md) — Symfony, Laravel, plain PHP and custom frameworks.
* [Migration guide: v1.x to v2.0](migration-guide.md) — map `populateConfig()` and `ApiAwareInterface` onto the new container.

### Where to start

| If you are... | Read |
|---|---|
| New to Payum 2.0 | [Getting started](getting-started.md) |
| Upgrading from Payum 1.x | [Migration guide](migration-guide.md) |
| Replacing the HTTP client, logger or token storage | [Customization](customization.md) |
| Wiring Payum into Symfony or Laravel | [Framework integration](framework-integration.md) |
| Writing your own gateway | [Gateways](../gateways/README.md), then [Develop a custom Payum gateway](../develop-gateway-with-payum.md) |
| Porting a 1.x gateway | [Migrating a gateway from 1.x](../gateways/migrating-from-v1.md) |

See [The Architecture](../the-architecture.md) for the model the container wires up, and [Services](../gateways/services.md) for what a 2.0 gateway gets from the container without declaring anything.

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/Payum)
