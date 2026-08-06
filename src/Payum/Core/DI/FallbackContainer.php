<?php

namespace Payum\Core\DI;

use DI\Container;
use Psr\Container\ContainerInterface;
use function array_merge;
use function array_unique;
use function array_values;

/**
 * Looks a service up in the primary container and falls back to the secondary one when the primary does
 * not have it.
 *
 * This lets an application hand its own container to Payum without having to re-declare every service
 * Payum needs: whatever the application defines wins, the rest comes from Payum's own defaults.
 */
final class FallbackContainer implements ContainerInterface
{
    public function __construct(
        private ContainerInterface $primary,
        private ContainerInterface $fallback
    ) {
    }

    public function get(string $id): mixed
    {
        return $this->primary->has($id) ?
            $this->primary->get($id) :
            $this->fallback->get($id);
    }

    public function has(string $id): bool
    {
        return $this->primary->has($id) || $this->fallback->has($id);
    }

    /**
     * The ids of both containers, as far as they are able to report them. A container which cannot
     * enumerate its entries contributes nothing.
     *
     * @return list<string>
     */
    public function getKnownEntryNames(): array
    {
        return array_values(array_unique(array_merge(
            self::knownEntryNamesOf($this->primary),
            self::knownEntryNamesOf($this->fallback),
        )));
    }

    /**
     * @return list<string>
     */
    private static function knownEntryNamesOf(ContainerInterface $container): array
    {
        if ($container instanceof self) {
            return $container->getKnownEntryNames();
        }

        if ($container instanceof Container) {
            return array_values($container->getKnownEntryNames());
        }

        return [];
    }
}
