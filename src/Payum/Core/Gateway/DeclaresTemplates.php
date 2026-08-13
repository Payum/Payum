<?php

declare(strict_types=1);

namespace Payum\Core\Gateway;

/**
 * Optional. Implement when a gateway ships templates of its own.
 *
 *     ['PayumAcme' => __DIR__ . '/Resources/views']
 *
 * Handlers then name a template as `@PayumAcme/obtain_token.html.twig`.
 *
 * Name the namespace after the gateway. Never use `PayumCore`: it replaces core's own templates
 * rather than adding to them, including the layout every Payum template extends.
 *
 * See docs/gateways/templates.md.
 */
interface DeclaresTemplates
{
    /**
     * @return array<string, string> namespace => directory
     */
    public function templatePaths(): array;
}
