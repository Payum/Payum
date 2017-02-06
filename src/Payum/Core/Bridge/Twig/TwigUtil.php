<?php
namespace Payum\Core\Bridge\Twig;

class TwigUtil
{
    /**
     * @var \SplObjectStorage
     */
    protected static $storage;
    
    /**
     * @param \Twig_Environment $twig
     * @param string[] $paths
     */
    public static function registerPaths(\Twig_Environment $twig, array $paths)
    {
        if (false == static::$storage) {
            static::$storage = new \SplObjectStorage();
        }
        
        $storage = static::$storage;

        /** @var \Twig_Loader_Filesystem $payumLoader */
        $payumLoader = $twig && isset($storage[$twig]) ? $storage[$twig] : new \Twig_Loader_Filesystem();
        foreach ($paths as $namespace => $path) {
            $payumLoader->addPath($path, $namespace);
        }

        if (false == isset($storage[$twig])) {
            $currentLoader = $twig->getLoader();
            if ($currentLoader instanceof \Twig_Loader_Chain) {
                $currentLoader->addLoader($payumLoader);
            } else {
                $twig->setLoader(new \Twig_Loader_Chain([$currentLoader, $payumLoader]));
            }

            $storage->attach($twig, $payumLoader);
        }
    }
}
