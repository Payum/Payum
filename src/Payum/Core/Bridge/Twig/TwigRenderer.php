<?php

declare(strict_types=1);

namespace Payum\Core\Bridge\Twig;

use Payum\Core\Template\RendererInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\ChainLoader;
use function array_replace;

/**
 * Renders with Twig, adding the layout every Payum template extends.
 */
final class TwigRenderer implements RendererInterface
{
    /**
     * Registers $paths and an absolute-path loader on $twig, mutating it.
     *
     * @param array<string, string> $paths namespace => directory
     *
     * @throws LoaderError
     */
    public function __construct(
        private readonly Environment $twig,
        private readonly string $layout,
        array $paths = [],
    ) {
        TwigUtil::registerPaths($twig, $paths);

        $loader = $twig->getLoader();

        if (! $loader instanceof ChainLoader) {
            $loader = new ChainLoader([$loader]);
            $twig->setLoader($loader);
        }

        foreach ($loader->getLoaders() as $registered) {
            if ($registered instanceof AbsolutePathLoader) {
                return;
            }
        }

        $loader->addLoader(new AbsolutePathLoader());
    }

    /**
     * @param string $template a resolved absolute file path, not a key
     */
    public function render(string $template, array $context = []): string
    {
        return $this->twig->render($template, array_replace([
            'layout' => $this->layout,
        ], $context));
    }
}
