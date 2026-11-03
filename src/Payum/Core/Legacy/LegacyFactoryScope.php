<?php

declare(strict_types=1);

namespace Payum\Core\Legacy;

/**
 * Marks core building a gateway through a 1.x factory. What core calls on its own behalf while it does
 * is deprecated too, but nobody outside core can act on that: the factory is reported once on the way
 * in, and the deprecations core reaches by itself stay quiet.
 *
 * @internal
 */
final class LegacyFactoryScope
{
    private static int $depth = 0;

    /**
     * @template T
     *
     * @param callable(): T $build
     *
     * @return T
     */
    public static function run(callable $build): mixed
    {
        ++self::$depth;

        try {
            return $build();
        } finally {
            --self::$depth;
        }
    }

    public static function isActive(): bool
    {
        return 0 < self::$depth;
    }
}
