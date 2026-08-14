<?php

declare(strict_types=1);

namespace Payum\Core\Gateway;

/**
 * Optional. Implement when a gateway ships templates of its own.
 *
 *     ['payum.template.acme.checkout' => __DIR__ . '/Resources/views/checkout.html.twig']
 *
 * Handlers name the key, never the file. An application overrides a template by rebinding the key.
 *
 * Keys are written out in full, by convention `payum.template.{gateway}.{name}`. Two gateways
 * declaring the same key is an error.
 *
 * See docs/gateways/templates.md.
 */
interface DeclaresTemplates
{
    /**
     * @return array<string, string> template key => absolute path to the template file
     */
    public function templates(): array;
}
