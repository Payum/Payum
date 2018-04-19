<?php
namespace Payum\Core\Bridge\Twig;

use Twig\Environment;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

class TwigUtil
{
    /**
     * @var \SplObjectStorage
     */
    protected static $storage;
    
    /**
     * @param Environment $twig
     * @param string[] $paths
     */
    public static function registerPaths(Environment $twig, array $paths)
    {
        if (false == static::$storage) {
            static::$storage = new \SplObjectStorage();
        }
        
        $storage = static::$storage;

        /** @var FilesystemLoader $payumLoader */
        $payumLoader = $twig && isset($storage[$twig]) ? $storage[$twig] : new FilesystemLoader();
        foreach ($paths as $namespace => $path) {
            $payumLoader->addPath($path, $namespace);
        }

        if (false == isset($storage[$twig])) {
            $currentLoader = $twig->getLoader();
            if ($currentLoader instanceof ChainLoader) {
                $currentLoader->addLoader($payumLoader);
            } else {
                $twig->setLoader(new ChainLoader([$currentLoader, $payumLoader]));
            }

            $storage->attach($twig, $payumLoader);
        }
    }
}
