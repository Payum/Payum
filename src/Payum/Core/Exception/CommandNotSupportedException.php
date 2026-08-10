<?php

declare(strict_types=1);

namespace Payum\Core\Exception;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Result\Result;
use function implode;
use function sprintf;

class CommandNotSupportedException extends InvalidArgumentException
{
    /**
     * @var CommandInterface<Result>
     */
    protected CommandInterface $command;

    protected ?string $gatewayName = null;

    protected ?string $gatewayClass = null;

    /**
     * @var list<class-string<CommandInterface<Result>>>
     */
    protected array $supportedCommands = [];

    /**
     * @param CommandInterface<Result> $command
     * @param string|null $gatewayName the name the gateway is registered under
     * @param list<class-string<CommandInterface<Result>>> $supportedCommands
     */
    public static function create(
        CommandInterface $command,
        ?string $gatewayName = null,
        ?PaymentGateway $gateway = null,
        array $supportedCommands = [],
    ): self {
        $exception = new self(self::buildMessage($command, $gatewayName, $gateway, $supportedCommands));

        $exception->command = $command;
        $exception->gatewayName = $gatewayName;
        $exception->gatewayClass = $gateway instanceof PaymentGateway ? $gateway::class : null;
        $exception->supportedCommands = $supportedCommands;

        return $exception;
    }

    /**
     * @return CommandInterface<Result>
     */
    public function getCommand(): CommandInterface
    {
        return $this->command;
    }

    /**
     * @return class-string<PaymentGateway>|null
     */
    public function getGatewayClass(): ?string
    {
        /** @var class-string<PaymentGateway>|null */
        return $this->gatewayClass;
    }

    /**
     * The name the gateway is registered under, which is what identifies it when the gateway is picked at
     * runtime. Null when the gateway was built outside the registry.
     */
    public function getGatewayName(): ?string
    {
        return $this->gatewayName;
    }

    /**
     * The commands this gateway does handle. Empty for a gateway still built from actions.
     *
     * @return list<class-string<CommandInterface<Result>>>
     */
    public function getSupportedCommands(): array
    {
        return $this->supportedCommands;
    }

    /**
     * @param CommandInterface<Result> $command
     * @param list<class-string<CommandInterface<Result>>> $supportedCommands
     */
    private static function buildMessage(
        CommandInterface $command,
        ?string $gatewayName,
        ?PaymentGateway $gateway,
        array $supportedCommands,
    ): string {
        $subject = match (true) {
            null !== $gatewayName && $gateway instanceof PaymentGateway => sprintf('Gateway "%s" (%s)', $gatewayName, $gateway->name()),
            null !== $gatewayName => sprintf('Gateway "%s"', $gatewayName),
            $gateway instanceof PaymentGateway => $gateway->name(),
            default => 'The gateway',
        };

        if ([] === $supportedCommands) {
            return sprintf(
                '%s handles no commands, so it cannot handle %s. It is built from actions: dispatch the matching Payum\Core\Request instead, or port it to handlers.',
                $subject,
                $command::class,
            );
        }

        return sprintf(
            '%s does not handle %s. It handles %s. Add a handler for %s to %s::handlers().',
            $subject,
            $command::class,
            implode(', ', $supportedCommands),
            $command::class,
            $gateway instanceof PaymentGateway ? $gateway::class : 'the gateway',
        );
    }
}
