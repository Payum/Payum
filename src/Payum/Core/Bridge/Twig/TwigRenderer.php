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
    }

    public function render(string $template, array $context = []): string
    {
        return $this->twig->render($template, array_replace([
            'layout' => $this->layout,
        ], $context));
    }
}
