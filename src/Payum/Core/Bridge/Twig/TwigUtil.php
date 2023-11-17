<?php

namespace Payum\Core\Bridge\Twig;

use SplObjectStorage;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

class TwigUtil
{
    /**
     * @var ?SplObjectStorage<object, mixed>
     */
    protected static ?SplObjectStorage $storage = null;

    /**
     * @param string[] $paths
     * @throws LoaderError
     */
    public static function registerPaths(Environment $twig, array $paths): void
    {
        if (! static::$storage) {
            static::$storage = new SplObjectStorage();
        }

        /** @var FilesystemLoader $payumLoader */
        $payumLoader = static::$storage[$twig] ?? new FilesystemLoader();
        foreach ($paths as $namespace => $path) {
            $payumLoader->addPath($path, $namespace);
        }

        if (! isset(static::$storage[$twig])) {
            $currentLoader = $twig->getLoader();
            if ($currentLoader instanceof ChainLoader) {
                $currentLoader->addLoader($payumLoader);
            } else {
                $twig->setLoader(new ChainLoader([$currentLoader, $payumLoader]));
            }

            static::$storage->attach($twig, $payumLoader);
        }
    }
}
