<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Exception\LogicException;
use Psr\Container\ContainerInterface;
use function is_a;
use function is_string;
use function sprintf;
use function usort;

/**
 * What is registered, and in what order it should run.
 *
 * Immutable: every method returns a new collection, so the global set can be layered with a gateway's own
 * without either being able to disturb the other.
 */
final class MiddlewareCollection
{
    /**
     * @var list<array{middleware: class-string<MiddlewareInterface>|MiddlewareInterface, priority: int|null}>
     */
    private readonly array $entries;

    /**
     * @param list<array{middleware: class-string<MiddlewareInterface>|MiddlewareInterface, priority: int|null}> $entries
     */
    public function __construct(array $entries = [])
    {
        $this->entries = $entries;
    }

    /**
     * @param class-string<MiddlewareInterface>|MiddlewareInterface $middleware a container id or an instance
     * @param int|null $priority overrides {@see HasPriority}, which in turn overrides the default of 0
     */
    public function with(string | MiddlewareInterface $middleware, ?int $priority = null): self
    {
        return new self([...$this->entries, [
            'middleware' => $middleware,
            'priority' => $priority,
        ]]);
    }

    /**
     * Entries from $other are appended, so on equal priority they run inside the ones already here.
     */
    public function merge(self $other): self
    {
        return new self([...$this->entries, ...$other->entries]);
    }

    public function isEmpty(): bool
    {
        return [] === $this->entries;
    }

    /**
     * Resolves every entry and orders it, outermost first.
     *
     * @return list<MiddlewareInterface>
     */
    public function resolve(ContainerInterface $container): array
    {
        $resolved = [];

        foreach ($this->entries as $index => $entry) {
            $middleware = $entry['middleware'];

            if (is_string($middleware)) {
                $middleware = $container->get($middleware);
            }

            if (! $middleware instanceof MiddlewareInterface) {
                throw new LogicException(sprintf(
                    '%s must be a %s.',
                    is_string($entry['middleware']) ? $entry['middleware'] : $entry['middleware']::class,
                    MiddlewareInterface::class,
                ));
            }

            $resolved[] = [
                'middleware' => $middleware,
                'priority' => $entry['priority'] ?? self::declaredPriorityOf($entry['middleware']),
                // Registration order breaks ties. usort is stable on PHP 8, but being explicit costs
                // nothing and does not rely on that.
                'index' => $index,
            ];
        }

        usort(
            $resolved,
            static fn (array $a, array $b): int => [$b['priority'], $a['index']] <=> [$a['priority'], $b['index']],
        );

        return array_column($resolved, 'middleware');
    }

    /**
     * @param class-string<MiddlewareInterface>|MiddlewareInterface $middleware
     */
    private static function declaredPriorityOf(string | MiddlewareInterface $middleware): int
    {
        $class = is_string($middleware) ? $middleware : $middleware::class;

        return is_a($class, HasPriority::class, true) ? $class::priority() : 0;
    }
}
