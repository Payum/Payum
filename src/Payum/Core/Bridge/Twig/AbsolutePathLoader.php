<?php

declare(strict_types=1);

namespace Payum\Core\Bridge\Twig;

use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;
use function file_get_contents;
use function filemtime;
use function is_file;
use function sprintf;

/**
 * Loads a template by absolute filesystem path, for names that are paths rather than Twig namespaces.
 */
final class AbsolutePathLoader implements LoaderInterface
{
    public function exists(string $name): bool
    {
        return is_file($name);
    }

    public function getCacheKey(string $name): string
    {
        return $name;
    }

    public function getSourceContext(string $name): Source
    {
        if (! is_file($name)) {
            throw new LoaderError(sprintf('Template "%s" does not exist.', $name));
        }

        $code = @file_get_contents($name);

        if (false === $code) {
            throw new LoaderError(sprintf('Template "%s" could not be read.', $name));
        }

        return new Source($code, $name, $name);
    }

    public function isFresh(string $name, int $time): bool
    {
        if (! is_file($name)) {
            throw new LoaderError(sprintf('Template "%s" does not exist.', $name));
        }

        $mtime = filemtime($name);

        if (false === $mtime) {
            throw new LoaderError(sprintf('Template "%s" could not be read.', $name));
        }

        return $mtime < $time;
    }
}
