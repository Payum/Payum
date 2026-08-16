<?php

declare(strict_types=1);

namespace Payum\Core\Bridge\Twig;

use Payum\Core\Template\RendererInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use function array_replace;

/**
 * Renders with Twig, adding the layout every Payum template extends.
 */
final class TwigRenderer implements RendererInterface
{
    /**
     * Registers $paths on $twig, mutating it.
     *
     * @param array<string, string|list<string>> $paths namespace => directory, or a list of directories
     *
     * @throws LoaderError
     */
    public function __construct(
        private readonly Environment $twig,
        private readonly string $layout,
        array $paths = [],
    ) {
        foreach ($paths as $namespace => $directories) {
            foreach ((array) $directories as $directory) {
                TwigUtil::registerPaths($twig, [
                    $namespace => $directory,
                ]);
            }
        }
    }

    /**
     * @param string $template a Twig template name, e.g. `@PayumAcme/checkout.html.twig`
     */
    public function render(string $template, array $context = []): string
    {
        return $this->twig->render($template, array_replace([
            'layout' => $this->layout,
        ], $context));
    }
}
