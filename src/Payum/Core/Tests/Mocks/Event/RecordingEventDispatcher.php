<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Mocks\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use function array_values;

final class RecordingEventDispatcher implements EventDispatcherInterface
{
    /**
     * @var list<object>
     */
    public array $dispatched = [];

    public function dispatch(object $event): object
    {
        $this->dispatched[] = $event;

        return $event;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return list<T>
     */
    public function ofType(string $class): array
    {
        return array_values(array_filter($this->dispatched, static fn (object $event): bool => $event instanceof $class));
    }

    /**
     * @return list<class-string>
     */
    public function classes(): array
    {
        return array_map(static fn (object $event): string => $event::class, $this->dispatched);
    }
}
