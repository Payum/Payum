<?php

declare(strict_types=1);

namespace Payum\Core\Template;

/**
 * Turns a template name and its context into markup.
 *
 * Core names a template and never renders one itself, which is what keeps the command path free of any
 * templating engine: Twig serves standalone and Symfony, and a Laravel integration binds a Blade
 * implementation against the same contract. {@see \Payum\Core\Result\NextAction\RenderTemplate} is
 * already data rather than rendered output, so nothing above this has to change.
 */
interface RendererInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function render(string $template, array $context = []): string;
}
