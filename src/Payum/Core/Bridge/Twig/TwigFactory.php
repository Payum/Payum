<?php

namespace Payum\Core\Bridge\Twig;

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
    public static function createGenericPaths()
    {
        return array_flip(array_filter([
            'PayumCore' => self::guessViewsPath(\Payum\Core\Gateway::class),
            'PayumStripe' => self::guessViewsPath(\Payum\Stripe\StripeJsGatewayFactory::class),
            'PayumKlarnaCheckout' => self::guessViewsPath(\Payum\Klarna\Checkout\KlarnaCheckoutGatewayFactory::class),
            'PayumSymfonyBridge' => self::guessViewsPath(\Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter::class),
        ]));
    }

    /**
     * @return Environment
     */
    public static function createGeneric()
    {
        $loader = new FilesystemLoader();
        foreach (static::createGenericPaths() as $path => $namespace) {
            $loader->addPath($path, $namespace);
        }

        return new Environment($loader);
    }

    /**
     * @param string $gatewayFactoryOrRootClass
     *
     * @return string|null
     */
    public static function guessViewsPath($gatewayFactoryOrRootClass)
    {
        if (false == class_exists($gatewayFactoryOrRootClass)) {
            return;
        }

        $rc = new \ReflectionClass($gatewayFactoryOrRootClass);

        return dirname($rc->getFileName()) . '/Resources/views';
    }
}
