<?php

declare(strict_types=1);

namespace Payum\Core\Gateway;

/**
 * Optional. Implement when a gateway ships templates of its own.
 *
 *     ['PayumAcme' => __DIR__ . '/Resources/views']
 *
 * Handlers then name `@PayumAcme/checkout.html.twig`, and templates under that directory can include and
 * import each other through the same namespace.
 *
 * Name the namespace after the gateway. `PayumCore` is reserved for Payum's own views and declaring it is
 * an error. Two gateways sharing a namespace is not an error: Twig searches a namespace's directories in
 * the order they were registered.
 *
 * See docs/gateways/templates.md.
 */
interface DeclaresTemplates
{
    /**
     * @return array<string, string> Twig namespace => directory
     */
    public function templateNamespaces(): array;
}
