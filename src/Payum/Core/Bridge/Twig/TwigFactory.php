<?php

namespace Payum\Core\Bridge\Twig;

use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Gateway;
use Payum\Klarna\Checkout\KlarnaCheckoutGatewayFactory;
use Payum\Stripe\StripeJsGatewayFactory;
use ReflectionClass;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * @deprecated since 1.0.0-BETA4
 */
class TwigFactory
{
    /**
     * @return string[]
     */
    public static function createGenericPaths(): array
    {
        return array_flip(array_filter([
            'PayumCore' => self::guessViewsPath(Gateway::class),
            'PayumStripe' => self::guessViewsPath(StripeJsGatewayFactory::class),
            'PayumKlarnaCheckout' => self::guessViewsPath(KlarnaCheckoutGatewayFactory::class),
            'PayumSymfonyBridge' => self::guessViewsPath(ReplyToSymfonyResponseConverter::class),
        ]));
    }

    public static function createGeneric(): Environment
    {
        $loader = new FilesystemLoader();
        foreach (static::createGenericPaths() as $path => $namespace) {
            $loader->addPath($path, $namespace);
        }

        return new Environment($loader);
    }

    /**
     * @return string|null
     */
    public static function guessViewsPath(string $gatewayFactoryOrRootClass)
    {
        if (false == class_exists($gatewayFactoryOrRootClass)) {
            return;
        }

        $rc = new ReflectionClass($gatewayFactoryOrRootClass);

        return dirname($rc->getFileName()) . '/Resources/views';
    }
}
