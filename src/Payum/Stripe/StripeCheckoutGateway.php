<?php

declare(strict_types=1);

namespace Payum\Stripe;

use League\Uri\Uri;
use Payum\Core\Gateway\Capability;
use Payum\Core\Gateway\DeclaresCapabilities;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Metadata\Logo;
use Payum\Stripe\Config\StripeCheckoutConfig;
use Payum\Stripe\Handler\CaptureHandler;

/**
 * STUB -- illustrates the gateway shape. Nothing here is wired up yet.
 *
 * This is what replaces StripeCheckoutGatewayFactory: no populateConfig(), no ArrayObject of defaults, no
 * payum.action.* keys. The gateway says what it is and names its handlers; core assembles the rest.
 *
 * No constructor, which is the rule rather than a coincidence -- core instantiates one of these purely to
 * read its metadata, so that an application can list installed gateways before anyone has entered an API
 * key. Credentials live in StripeCheckoutConfig and are resolved from the container.
 *
 * It does not implement ContainerConfiguration either, because it needs no definitions: StripeCheckoutApi
 * takes only container entries, so PHP-DI autowires it. Compare Paypal, which does.
 */
final class StripeCheckoutGateway implements GatewayInterface, DeclaresCapabilities
{
    /**
     * Only the nuance. Capture is deliberately absent: core derives it from handlers(), so the two cannot
     * drift the way a hand-maintained list would.
     */
    public function capabilities(): array
    {
        return [
            Capability::MultiCurrency,
            Capability::ThreeDSecure,
            Capability::Webhooks,
        ];
    }

    public function configClass(): string
    {
        return StripeCheckoutConfig::class;
    }

    public function handlers(): array
    {
        return [
            CaptureHandler::class,
        ];
    }

    public function logo(): Logo
    {
        return Logo\Url::create('https://cdn.brandfetch.io/idxAg10C0L/theme/dark/logo.svg?c=1dxbfHSJFAPEGdCLU4o5B');
    }

    public function name(): string
    {
        return 'Stripe Checkout';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://stripe.com');
    }
}
