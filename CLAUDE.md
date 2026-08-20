# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Payum is a framework-agnostic PHP payment processing library. Every payment operation goes through a
**Gateway** that routes **Request** objects to **Action** objects, which may interrupt the flow by throwing
**Reply** exceptions (redirects, forms, raw responses).

- **Monorepo**: `src/Payum/Core` plus one directory per gateway package. Each package has its own
  `composer.json` and is published as a read-only split repo (`Payum/Core`, `Payum/Stripe`, …).
- **Gateways in this repo**: AuthorizeNet/Aim, Be2Bill, Klarna (Checkout, Invoice), Offline, Payex,
  Paypal (ExpressCheckout, ProCheckout, ProHosted, Masspay, Ipn, Rest), Sofort, Stripe (Checkout, Js,
  Direct), plus `Skeleton/` as the template for new ones. The "50+ gateways" figure comes from the Omnipay
  bridge (`payum/omnipay-v3-bridge`) and third-party packages, not from this repo alone.
- **PHP 8.1+**; default branch is `2.x` (renamed from `master`).


### Testing

```bash
# Full suite (~3200 tests). Both vars matter. Without SYMFONY_DEPRECATIONS_HELPER the run exits 1 on
# unsilenced deprecation notices from third-party SDKs even though every test passes (CI sets it
# globally). Without XDEBUG_MODE=off, MiddlewarePipelineTest::testShouldStopAHandlerThatDispatches-
# ItsWayIntoALoop fails outright — Xdebug's 512-frame nesting limit throws before Payum's own
# EndlessCycleDetector can. That failure is an artefact, not a regression.
XDEBUG_MODE=off SYMFONY_DEPRECATIONS_HELPER=weak vendor/bin/phpunit

# One component / one class / one method
vendor/bin/phpunit src/Payum/Core/Tests
vendor/bin/phpunit src/Payum/Core/Tests/GatewayTest.php
vendor/bin/phpunit --filter testMethodName src/Payum/Core/Tests/GatewayTest.php
```

`phpunit.xml.dist` sets `failOnRisky` and `stopOnRisky`, so a risky test aborts the run.

### Code quality (all three are CI gates)

```bash
vendor/bin/phpstan                     # level 6, reads phpstan-baseline.neon
vendor/bin/ecs check                   # coding standards
vendor/bin/ecs check --fix             # autofix
vendor/bin/rector process --dry-run    # CI runs this report-only
vendor/bin/rector process              # apply
```

CI runs PHPStan with `doctrine/mongodb-odm` installed (`composer require doctrine/mongodb-odm
--with-all-dependencies`); locally, without it, the ODM-related results differ.

## Architecture

### Execution flow

`Gateway::execute()` (`src/Payum/Core/Gateway.php`) is the whole runtime in ~50 lines:

1. Push a `Context` onto a stack (nested `execute()` calls make this a stack — that is what
   `EndlessCycleDetectorExtension` inspects).
2. `extensions->onPreExecute()`, then `findActionSupported()` picks the first action whose
   `supports($request)` returns true, then `extensions->onExecute()`.
3. `$action->execute($request)`, then `onPostExecute()`.
4. A thrown `ReplyInterface` is caught, passed to `onPostExecute()` (extensions may swap or clear it), then
   re-thrown — unless `execute($request, true)` was used, which returns the reply instead.
5. Other exceptions go through `onPostExecuteWithException()`, which preserves the exception chain the way
   Symfony's `ExceptionListener` does.

Actions delegate by creating sub-requests: implement `GatewayAwareInterface`, use `GatewayAwareTrait`, then
`$this->gateway->execute(new ObtainCreditCard(...))`.

### Two generations of gateway wiring (important)

This is the main thing in flight on `2.x`. Both mechanisms are live at once.

**Legacy (v1) config-array style — still what every shipped gateway factory uses:**

- `{Name}GatewayFactory extends GatewayFactory` and overrides `populateConfig(ArrayObject $config)`.
- `GatewayFactory::createConfig()` layers config: caller's config → factory defaults → `CoreGatewayFactory`
  config.
- `CoreGatewayFactory::create()` builds a PHP-DI container out of that flat array, then binds services to
  the gateway **by key prefix**: `payum.action.*` → `addAction()`, `payum.extension.*` → `addExtension()`,
  `payum.api.*` → `addApi()` (deprecated). Ordering is controlled by `payum.prepend_actions` /
  `payum.prepend_extensions` or by implementing `PrependActionInterface` / `PrependExtensionInterface`.
- `create()`, `createConfig()`, `buildActions()`, `buildApis()`, `buildExtensions()` and `buildClosures()`
  all emit deprecations now. Do not build new features on them.

**Current (v2) container style:**

- `Payum\Core\DI\ContainerConfiguration` is the target interface: `configureContainer(): array` returns
  PHP-DI definitions, `createGateway(ContainerInterface): Gateway` assembles the gateway.
- Only `CoreGatewayFactory` implements it so far; `GatewayFactory` carries a comment saying it will
  implement it (and drop `GatewayFactoryInterface`) in 3.0. `PayumBuilder` emits a deprecation for every
  factory that still lacks it.
- `PayumBuilder::getPayum()` builds a **global container** with the services shared by all gateways (token
  storage, `TokenFactoryInterface`, `GenericTokenFactoryInterface`, `HttpRequestVerifierInterface`, PSR-18
  client and PSR-17 factories, storage extensions). Then per gateway it layers a container:
  factory defaults → shared services → that gateway's config (last wins), and wraps it in
  `FallbackContainer($gatewayContainer, $globalContainer)` so anything the gateway container cannot resolve
  is looked up globally.
- `PayumBuilder::setGlobalContainer()` puts an application's own container *in front of* Payum's defaults,
  so a framework only declares what it wants to override. `addGlobalService()` adds single entries.

Read `docs/di/` (getting-started, customization, framework-integration, migration-guide) before changing
container wiring — it documents the intended end state.

### API injection

`ApiAwareInterface` / `ApiAwareTrait` / `Gateway::addApi()` / `payum.api.*` are deprecated in 2.0 and go
away in 3.0. New actions take their API through the constructor:

```php
class CaptureAction implements ActionInterface
{
    public function __construct(private readonly StripeApi $api) {}
}
```

### Storage and tokens

Models implement marker interfaces (`PaymentInterface`, `TokenInterface`, `GatewayConfigInterface`).
Implementations: `Bridge/Doctrine/Storage/DoctrineStorage` (ORM/ODM),
`Bridge/Laminas/Storage/TableGatewayStorage`, `Storage/FilesystemStorage` (tests only, not production
safe), and `Storage/CryptoStorageDecorator` for encrypted gateway configs.

Tokens produce single-use URLs (`$tokenFactory->createCaptureToken('gateway', $payment, 'afterUrl')`) and
are verified by `HttpRequestVerifier`. `StorageExtension` auto-persists models;
`GenericTokenFactoryExtension` injects the token factory into requests that ask for it.

## Conventions

- **Deprecations**: new ones use `trigger_deprecation('payum/core', '2.0.0', ...)`
  (symfony/deprecation-contracts); older code still uses `@trigger_error(..., E_USER_DEPRECATED)`. Anything
  deprecated in 2.0 is removed in 3.0 — say so in the message.
- **Coding standards** (`ecs.php`, applied to `src/`, `rector.php`, `ecs.php`): PSR-12 + `common` +
  `cleanCode` prepared sets, Yoda conditions, single quotes, ordered class elements, `void` return types,
  one const/property per statement. `MethodChainingNewlineFixer`, `TypesSpacesFixer` and
  `UnaryOperatorSpacesFixer` are deliberately skipped. `declare(strict_types=1);` in new files.
- **Rector** (`rector.php`) targets `PhpVersion::PHP_82` over `src/` only. Constructor promotion,
  readonly-property and `never`-return rectors are skipped on purpose, as are three PHPUnit
  `withConsecutive` rules — the file explains why (their rewrites are wrong against PHPUnit 9.6). Do not
  re-enable those while the suite is on PHPUnit 9.6.
- **PHPStan** is level 6 with a large `phpstan-baseline.neon`. Write new code baseline-clean; don't
  regenerate the whole baseline to hide a new error.
- **Comments and docblocks are minimal.** Write the code so it reads clearly, then comment only what the
  code cannot say: a non-local constraint, an ordering that matters, a workaround for outside behaviour, a
  subtlety a reader would otherwise get wrong. A comment restating the line below it is noise. Docblocks
  carry `@param` / `@return` / `@throws` and the generics PHPStan needs, plus a one-line summary only when
  the name is not self-explanatory — API prose belongs in `docs/`. Two things comments must never do:
  **narrate the change** (what it used to be, what was considered, why one option was picked over another —
  that is the commit message or the PR), and **reference a discussion** ("this is deliberate", "as
  decided", "rather than X"). A reader six months from now has only the code.
- **Tests** mirror the source tree (`src/Payum/Core/Foo.php` → `src/Payum/Core/Tests/FooTest.php`) and run
  on PHPUnit 9.6. `autoload.php.dist` (and each package's `Tests/bootstrap.php`) registers the
  `Payum\Core\Tests` namespace so gateway packages can extend Core's shared bases — `GenericActionTest`,
  `AbstractGatewayFactoryTest`. External APIs are mocked; `phpunit.xml.dist` holds sandbox credentials for
  the few integration-style tests.

## Monorepo, CI and release

- **Splitting**: `scripts/subtree-split [branch]` pushes each `src/Payum/*` prefix to its own GitHub repo, and
  the push is **forced** — the split repos are generated, so anything on the branch that is not the split
  is stale (a renamed branch, a rewritten commit) and a rejected push would just wedge the workflow.
  `scripts/release <tag> [branch]` runs a whole release: it tags this repo and publishes its GitHub release,
  then signs and pushes that tag to every downstream repo and creates a release there too. Every step is
  skipped if already done, so re-running is safe. Omitting `[branch]` derives it from the version — `1.7.8`
  → `1.7.x`, `2.0.0` → `2.x` (`X.Y.x` first, then `X.x`) — which is what lets a closed milestone, whose
  title is only a version, drive the release. `scrpipts/subsplits` is the shared prefix → remote → repository
  manifest both scripts read; add new packages there, not in the scripts. Dependency or metadata changes
  must be made in **both** the root `composer.json` and the affected package `composer.json`.
- **splitsh-lite** is not committed; `scripts/install-splitsh-lite` downloads the pinned v1.0.1 build (verified
  by checksum) into `scripts/splitsh-lite`. Do **not** swap it for `git subtree split`: the two no longer agree,
  and git 2.55 puts 6 of the 16 packages on different commits, which would rewrite the split repos'
  published history. No binary exists for darwin/arm64 — run the workflows instead of splitting locally.
- **CI** (`.github/workflows/`): `ci.yaml` runs the suite on PHP 8.1–8.5, again with `--prefer-lowest`, and
  a MongoDB matrix (server 6.0/7.0/8.0 × ext-mongodb 1.x/2.x); `cs.yaml` runs PHPStan and ECS;
  `rector.yaml` runs Rector in `--dry-run` as a report-only gate. `milestone.yaml` calls
  `SolidWorx/actions/.github/workflows/pr-milestone.yml@main`, which puts a pull request on the open
  milestone of the branch it targets by inverting the milestone → branch mapping `scripts/release`
  uses, so each release branch needs exactly one open milestone and the check fails when it has none
  or more than one. That workflow is tracked at `@main` on purpose — SolidWorx/actions is published
  without release tags — so a change there reaches this repository on its next run.
  `subtree-split.yaml` runs on push to
  `2.x`/`1.x`/`1.7.x` and can be dispatched manually against any branch. `release.yaml` is driven by
  **closing a milestone** — the title is the version — and also accepts a published release or a manual
  dispatch with a tag; it needs `GPG_PRIVATE_KEY`/`GPG_PASSPHRASE` on top of `ACCESS_TOKEN`,
  `SSH_PRIVATE_KEY` and `KNOWN_HOSTS`. It creates this repo's own release with `GITHUB_TOKEN` on purpose:
  doing it with `ACCESS_TOKEN` would emit a release event and run the workflow a second time.

## Documentation

`docs/` is the source of the GitBook at https://payum.gitbook.io/payum/. Start with
`docs/the-architecture.md` for the request/action/reply model and `docs/di/README.md` for the 2.0 container
work. `docs/SUMMARY.md` is the GitBook table of contents — new pages must be linked there to be published.

Write for someone using Payum for the first time, not for someone who knows its internals. Say what to do
and what to avoid; a caveat is worth stating when a user would otherwise get it wrong ("never name your
template namespace `PayumCore`"), the mechanism behind it usually is not. Explain internals only where a
user cannot succeed without them, and keep integrator-level material in clearly marked advanced sections.
Every code sample must actually run — check it against the source rather than assuming.
