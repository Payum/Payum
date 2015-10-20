<?php
namespace Payum\Core\Bridge\Twig;

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
        return array_flip(array_filter(array(
            'PayumCore' => self::guessViewsPath('Payum\Core\Gateway'),
            'PayumStripe' => self::guessViewsPath('Payum\Stripe\StripeJsGatewayFactory'),
            'PayumKlarnaCheckout' => self::guessViewsPath('Payum\Klarna\Checkout\KlarnaCheckoutGatewayFactory'),
            'PayumSymfonyBridge' => self::guessViewsPath('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter'),
        )));
    }

    /**
     * @return \Twig_Environment
     */
    public static function createGeneric()
    {
        $loader = new \Twig_Loader_Filesystem();
        foreach (static::createGenericPaths() as $path => $namespace) {
            $loader->addPath($path, $namespace);
        }

        return new \Twig_Environment($loader);
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

        return dirname($rc->getFileName()).'/Resources/views';
    }
}
