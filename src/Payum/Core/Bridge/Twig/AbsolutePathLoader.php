<?php

declare(strict_types=1);

namespace Payum\Core\Bridge\Twig;

use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;
use function file_get_contents;
use function filemtime;
use function in_array;
use function is_file;
use function sprintf;

/**
 * Loads a template by absolute filesystem path, for names that are paths rather than Twig namespaces.
 *
 * Restricted to an allowlist: the environment this is attached to may be an application's shared Twig
 * service, and without one this would let any code sharing that environment read arbitrary files by path.
 */
final class AbsolutePathLoader implements LoaderInterface
{
    /**
     * @param list<string> $files the only absolute paths this loader will serve
     */
    public function __construct(
        private readonly array $files,
    ) {
    }

    public function exists(string $name): bool
    {
        return in_array($name, $this->files, true) && is_file($name);
    }

    public function getCacheKey(string $name): string
    {
        return $name;
    }

    public function getSourceContext(string $name): Source
    {
        if (! in_array($name, $this->files, true)) {
            throw new LoaderError(sprintf('Template "%s" is not registered.', $name));
        }

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
        if (! in_array($name, $this->files, true)) {
            throw new LoaderError(sprintf('Template "%s" is not registered.', $name));
        }

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
