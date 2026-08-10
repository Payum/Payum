<?php

declare(strict_types=1);

namespace Payum\Core\Handler;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Result\Result;
use ReflectionMethod;
use ReflectionNamedType;
use function is_subclass_of;
use function sprintf;

/**
 * Maps a command to the handler interface that answers it.
 *
 * Built by reflecting each handler's per-command interface rather than the handler itself, so a handler
 * cannot claim a command it does not implement. The value is the interface, not the concrete class, so
 * the container stays free to decorate it.
 */
final class HandlerMap
{
    /**
     * @param array<class-string<CommandInterface<Result>>, class-string<HandlerInterface>> $map
     * @param array<class-string<HandlerInterface>, class-string<HandlerInterface>> $bindings
     */
    private function __construct(
        private readonly array $map,
        private readonly array $bindings
    ) {
    }

    /**
     * @param list<class-string<HandlerInterface>> $handlerClasses
     */
    public static function fromHandlers(array $handlerClasses): self
    {
        $map = [];
        $bindings = [];

        foreach ($handlerClasses as $handlerClass) {
            $interfaces = self::handlerInterfacesOf($handlerClass);

            if ([] === $interfaces) {
                throw new LogicException(sprintf(
                    '%s implements no handler interface, so there is no way to tell which command it handles. Implement one of the %s children, such as %s.',
                    $handlerClass,
                    HandlerInterface::class,
                    CaptureHandlerInterface::class,
                ));
            }

            foreach ($interfaces as $interface) {
                $commandClass = self::commandOf($interface);

                if (isset($map[$commandClass])) {
                    throw new LogicException(sprintf(
                        'Both %s and %s handle %s. A command may only have one handler.',
                        $map[$commandClass],
                        $interface,
                        $commandClass,
                    ));
                }

                $map[$commandClass] = $interface;
                $bindings[$interface] = $handlerClass;
            }
        }

        return new self($map, $bindings);
    }

    /**
     * Handler interface to the concrete class implementing it, for the container to bind.
     *
     * @return array<class-string<HandlerInterface>, class-string<HandlerInterface>>
     */
    public function bindings(): array
    {
        return $this->bindings;
    }

    /**
     * The operation capabilities this gateway has by virtue of the handlers it ships.
     *
     * @return list<Capability>
     */
    public function capabilities(): array
    {
        $capabilities = [];

        foreach ($this->commands() as $commandClass) {
            $capabilities[] = $commandClass::capability();
        }

        return $capabilities;
    }

    /**
     * @return list<class-string<CommandInterface<Result>>>
     */
    public function commands(): array
    {
        return array_keys($this->map);
    }

    /**
     * The container id to resolve for a command, or null when this gateway does not handle it.
     *
     * @param class-string<CommandInterface<Result>> $commandClass
     *
     * @return class-string<HandlerInterface>|null
     */
    public function serviceIdFor(string $commandClass): ?string
    {
        return $this->map[$commandClass] ?? null;
    }

    /**
     * @param class-string<HandlerInterface> $interface
     *
     * @return class-string<CommandInterface<Result>>
     */
    private static function commandOf(string $interface): string
    {
        if (! method_exists($interface, 'handle')) {
            throw new LogicException(sprintf('%s must declare handle().', $interface));
        }

        $parameters = (new ReflectionMethod($interface, 'handle'))->getParameters();
        $type = ($parameters[0] ?? null)?->getType();

        if (! $type instanceof ReflectionNamedType || ! is_subclass_of($type->getName(), CommandInterface::class)) {
            throw new LogicException(sprintf(
                '%s::handle() must take a %s as its first parameter.',
                $interface,
                CommandInterface::class,
            ));
        }

        /** @var class-string<CommandInterface<Result>> */
        return $type->getName();
    }

    /**
     * @param class-string<HandlerInterface> $handlerClass
     *
     * @return list<class-string<HandlerInterface>>
     */
    private static function handlerInterfacesOf(string $handlerClass): array
    {
        $interfaces = [];

        foreach (class_implements($handlerClass) ?: [] as $interface) {
            if (is_subclass_of($interface, HandlerInterface::class)) {
                $interfaces[] = $interface;
            }
        }

        return $interfaces;
    }
}
