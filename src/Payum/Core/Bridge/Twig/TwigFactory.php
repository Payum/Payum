<?php
namespace Payum\Core\Bridge\Twig;

class TwigFactory
{
    /**
     * @return string[]
     */
    public static function createGenericPaths()
    {
        return array_flip(array_filter(array(
            'PayumCore' => self::guessViewsPath('Payum\Core\Payment'),
            'PayumStripe' => self::guessViewsPath('Payum\Stripe\JsPaymentFactory'),
            'PayumKlarnaCheckout' => self::guessViewsPath('Payum\Klarna\Checkout\PaymentFactory'),
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
     * @param string $paymentFactoryOrRootClass
     *
     * @return string|null
     */
    public static function guessViewsPath($paymentFactoryOrRootClass)
    {
        if (false == class_exists($paymentFactoryOrRootClass)) {
            return;
        }

        $rc = new \ReflectionClass($paymentFactoryOrRootClass);

        return dirname($rc->getFileName()).'/Resources/views';
    }
}
