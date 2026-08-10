<?php

declare(strict_types=1);

namespace Payum\Paypal\ExpressCheckout\Nvp;

use League\Uri\Uri;
use Payum\Core\DI\ContainerConfiguration;
use Payum\Core\Gateway\Capability;
use Payum\Core\Gateway\DeclaresCapabilities;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Metadata\Logo;
use Payum\Paypal\ExpressCheckout\Nvp\Config\ExpressCheckoutConfig;
use Payum\Paypal\ExpressCheckout\Nvp\Handler\CaptureHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * STUB -- illustrates a gateway that has to declare a service. Nothing here is wired up yet.
 *
 * Stripe's equivalent implements neither ContainerConfiguration nor anything else, because its Api
 * autowires. This one cannot: the v1 Nvp\Api takes `array $options` as its first parameter, and an array
 * has no type for PHP-DI to resolve.
 *
 * Note that this is a v1 artefact rather than something the container model is missing. When the Api is
 * reshaped to take ExpressCheckoutConfig it will autowire too, and the ContainerConfiguration below can
 * go. Until then the definition belongs here -- on the gateway that needs it -- which is exactly what
 * ContainerConfiguration is for.
 *
 * Note also the constructor that is not here: metadata must be readable without credentials.
 */
final class ExpressCheckoutGateway implements GatewayInterface, DeclaresCapabilities, ContainerConfiguration
{
    /**
     * Capture is deliberately absent: core derives it from handlers().
     */
    public function capabilities(): array
    {
        return [
            Capability::MultiCurrency,
            Capability::PartialRefund,
            Capability::Webhooks,
        ];
    }

    public function configClass(): string
    {
        return ExpressCheckoutConfig::class;
    }

    /**
     * @return array<string, mixed>
     */
    public function configureContainer(): array
    {
        return [
            Api::class => static fn (ContainerInterface $container): Api => new Api(
                $container->get(ExpressCheckoutConfig::class)->toArray(),
                $container->get(ClientInterface::class),
                $container->get(RequestFactoryInterface::class),
                $container->get(StreamFactoryInterface::class),
            ),
        ];
    }

    public function handlers(): array
    {
        return [
            CaptureHandler::class,
        ];
    }

    public function logo(): Logo
    {
        return Logo\Url::create('https://www.paypalobjects.com/webstatic/icon/pp258.png');
    }

    public function name(): string
    {
        return 'Paypal Express Checkout';
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://developer.paypal.com/api/nvp-soap/express-checkout/');
    }
}
