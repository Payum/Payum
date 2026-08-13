<?php

declare(strict_types=1);

namespace Payum\Core\Template;

/**
 * Turns a template name and its context into markup.
 */
interface RendererInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function render(string $template, array $context = []): string;
}
