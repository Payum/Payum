---
cover: .gitbook/assets/payum-bg.jpeg
coverY: 0
layout:
  cover:
    visible: true
    size: full
  title:
    visible: true
  description:
    visible: true
  tableOfContents:
    visible: true
  outline:
    visible: true
  pagination:
    visible: true
---

# Payum

Payum is a PHP library for taking payments. It gives your application one API for every payment
provider: you execute a command such as capture or refund on a gateway, and you get back a result
that says what happened and what the customer has to do next. To switch providers, you register
a different gateway and keep your checkout code.

Payum works in plain PHP and in any framework. It needs PHP 8.1 or later.

## What Payum gives you

- Commands for each payment operation: authorize, capture, refund, cancel, payout, sync and
  notifications from the provider.
- One set of payment statuses, written onto your own payment model after each operation.
- Single-use, signed URLs (tokens) that carry the customer from your site to the provider and back.
- Storages for Doctrine ORM, Doctrine MongoDB ODM and Laminas DB.
- A dependency injection container per gateway, so you can share or replace services such as the
  HTTP client or the logger.
- Middleware and PSR-14 events around every operation.

## Where to start

**Getting started.** [Get started](get-it-started.md) takes you from `composer require` to a
working payment in four short scripts. [How Payum works](the-architecture.md) explains gateways,
commands, results and tokens. [Supported gateways](supported-gateways.md) lists the gateway packages.

**Taking payments.** [Payment operations](payment-operations.md) shows every command from the
application's side, and [Payment status](payment-status.md) explains the statuses. The other pages
in this group cover [tokens](tokens.md), [notifications from the provider](instant-payment-notification.md),
[money and currencies](money.md) and [using your own payment model](your-order-integration.md).

**Configuring Payum.** [Configure Payum](di/getting-started.md) covers `PayumBuilder` and its
container. This group also covers [storages](storages.md),
[gateway configs stored in a database](configure-gateway-in-backend.md),
[payment pages](payment-pages.md), [events](events.md), [logging](logger.md) and
[troubleshooting](troubleshooting.md).

**Writing a gateway.** If your provider has no gateway yet, [Build a gateway](develop-gateway-with-payum.md)
walks you through one, and the [Gateways](gateways/README.md) chapter is the reference.

**Upgrading.** Coming from Payum 1.x? Read [Upgrade from 1.x](upgrading.md), and
[Migrate a gateway from 1.x](gateways/migrating-from-v1.md) if you maintain a gateway.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
