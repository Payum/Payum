<?php

declare(strict_types=1);

namespace Payum\Core\Template;

use Payum\Core\Exception\LogicException;
use function array_keys;
use function basename;
use function implode;
use function sprintf;
use function str_ends_with;
use function strlen;
use function usort;

/**
 * Resolves a template key to a file and renders it with whichever renderer handles that file type.
 */
final class TemplateRenderer implements RendererInterface
{
    /**
     * @param array<string, string> $templates template key => absolute file path
     * @param array<string, RendererInterface> $renderers file extension, without a leading dot => renderer
     */
    public function __construct(
        private readonly array $templates,
        private readonly array $renderers,
    ) {
    }

    public function render(string $template, array $context = []): string
    {
        if (! isset($this->templates[$template])) {
            throw new LogicException(sprintf(
                'No template is registered under "%s". Registered keys: %s.',
                $template,
                [] === $this->templates ? 'none' : implode(', ', array_keys($this->templates)),
            ));
        }

        $file = $this->templates[$template];

        return $this->rendererFor($file)->render($file, $context);
    }

    private function rendererFor(string $file): RendererInterface
    {
        $name = basename($file);

        $extensions = array_keys($this->renderers);
        usort($extensions, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($extensions as $extension) {
            if (str_ends_with($name, '.' . $extension)) {
                return $this->renderers[$extension];
            }
        }

        throw new LogicException(sprintf(
            'No renderer is registered for "%s", which %s resolves to. Register one with PayumBuilder::addRenderer().',
            $name,
            $file,
        ));
    }
}
