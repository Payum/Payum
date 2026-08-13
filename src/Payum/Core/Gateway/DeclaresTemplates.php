<?php

declare(strict_types=1);

namespace Payum\Core\Gateway;

/**
 * Optional. Implement when a gateway ships templates of its own.
 *
 * Namespace to directory is the one shape both Twig (`@PayumAcme/obtain_token.html.twig`) and Blade
 * (`View::addNamespace()`) understand, so the declaration itself says nothing about which engine
 * renders it. The template *name* a handler puts on a RenderTemplate still does -- see
 * docs/gateways/templates.md.
 *
 * A namespace declared here wins over the same namespace in `payum.paths`, so a gateway can override
 * a template core ships. That precedence is decided per gateway container, and holds cleanly under
 * core's default wiring, where each gateway builds its own Twig `Environment`. An application that
 * shares one `Environment` across gateways shares the namespaces registered on it too -- `TwigUtil`
 * keeps a single loader per `Environment` in a static map, so the precedence above becomes a race
 * between whichever gateway registers its paths first.
 */
interface DeclaresTemplates
{
    /**
     * @return array<string, string> namespace => directory
     */
    public function templatePaths(): array;
}
