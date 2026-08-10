<?php

declare(strict_types=1);

namespace Payum\Core\Exception;

use Payum\Core\Result\Result;
use Payum\Core\Command\CommandInterface;
use function sprintf;

class CommandNotSupportedException extends InvalidArgumentException
{
    /**
     * @var CommandInterface<Result>
     */
    protected CommandInterface $command;

    /**
     * @param CommandInterface<Result> $command
     */
    public static function create(CommandInterface $command): self
    {
        $exception = new self(sprintf(
            'The gateway does not handle %s. Add a handler for it to the gateway\'s handlers().',
            $command::class,
        ));

        $exception->command = $command;

        return $exception;
    }

    /**
     * @return CommandInterface<Result>
     */
    public function getCommand(): CommandInterface
    {
        return $this->command;
    }
}
