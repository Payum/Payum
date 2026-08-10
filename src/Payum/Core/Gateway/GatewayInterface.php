<?php

declare(strict_types=1);

namespace Payum\Core\Gateway;

use League\Uri\Uri;
use Payum\Core\Config\GatewayConfig;
use Payum\Core\DI\ContainerConfiguration;
use Payum\Core\Handler\HandlerInterface;
use Payum\Core\Metadata\Logo;

/**
 * A gateway: what it is called, what it looks like, how it is configured, and what it can do.
 *
 * This replaces the gateway factory. Where v1 had a factory whose job was to assemble a flat config array
 * and stuff services into a Gateway by key prefix, a v2 gateway simply describes itself and names its
 * handlers; core does the assembling.
 *
 * Optional companions, implemented only when needed:
 *
 *   {@see DeclaresCapabilities}  -- nuance beyond what handlers() implies
 *   {@see ContainerConfiguration} -- service definitions: an Api that cannot be autowired, a decorated
 *                                   handler, a second API version
 *
 * **A gateway must be constructible with no required arguments.** Core instantiates one purely to read
 * its metadata -- that is what lets an application render an "add a payment method" screen listing every
 * installed gateway before any credentials exist. Credentials belong to {@see GatewayConfig}, which is
 * resolved from the container, not injected here. A consequence worth stating: metadata cannot vary with
 * configuration, so there is no per-merchant title and no capability that depends on the account. If that
 * is ever needed it wants its own design rather than a constructor argument.
 *
 * Not to be confused with {@see \Payum\Core\GatewayInterface}, which is the executor -- the thing with
 * execute() on it. A file needing both aliases one of the two.
 */
interface GatewayInterface
{
    /**
     * The config class this gateway is configured with.
     *
     * Lets an admin UI render a credentials form for a gateway nobody has configured yet.
     * {@see GatewayConfig::getGatewayClass()} is the same edge in the other direction, which is what
     * PayumBuilder follows when an application registers a config; a conformance test asserts they agree.
     *
     * @return class-string<GatewayConfig>
     */
    public function configClass(): string;

    /**
     * The handlers this gateway ships, in no particular order.
     *
     * Core reflects each class to find which per-command handler interface it implements, and reads that
     * interface's handle() signature to key the command => handler map. Because PHP will not let one
     * class declare handle() twice, a handler serves exactly one command.
     *
     * @return list<class-string<HandlerInterface>>
     */
    public function handlers(): array;

    /**
     * Resolves to a path (local filesystem), a URL (publicly accessible), or a base64-encoded value.
     *
     * Logo\Url::create('https://example.com/logo.svg')
     * Logo\Path::create('../some/path/to/logo.svg')
     * Logo\Base64Encode::create('data:image/svg+xml;base64,PHN2Zy...')
     */
    public function logo(): Logo;

    /**
     * Human-readable name, e.g. 'Stripe Checkout'.
     */
    public function name(): string;

    /**
     * The gateway's website or developer documentation.
     */
    public function websiteUrl(): Uri;
}
