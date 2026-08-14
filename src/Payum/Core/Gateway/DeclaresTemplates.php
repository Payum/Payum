<?php

declare(strict_types=1);

namespace Payum\Core\Gateway;

/**
 * Optional. Implement when a gateway ships templates of its own.
 *
 *     [
 *         'PayumAcme' => __DIR__ . '/Resources/views',
 *         'payum.template.acme.checkout' => __DIR__ . '/Resources/views/checkout.html.twig',
 *     ]
 *
 * A directory registers a Twig namespace, so handlers can name `@PayumAcme/checkout.html.twig` and
 * templates can include each other. A file registers a template key, which an application can rebind to
 * a template of its own.
 *
 * Keys are written out in full, by convention `payum.template.{gateway}.{name}`. Two gateways declaring
 * the same key is an error; sharing a namespace is not.
 *
 * See docs/gateways/templates.md.
 */
interface DeclaresTemplates
{
    /**
     * @return array<string, string> template key => absolute file path, or namespace => directory
     */
    public function templates(): array;
}
