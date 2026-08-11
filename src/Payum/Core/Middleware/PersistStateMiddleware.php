<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\Result;
use Payum\Core\Security\TokenInterface;

/**
 * Writes the PSP state a handler produced back onto the payment.
 */
final class PersistStateMiddleware implements MiddlewareInterface, HasPriority
{
    /**
     * @param StorageRegistryInterface<object>|null $storages
     */
    public function __construct(
        private readonly ?StorageRegistryInterface $storages = null
    ) {
    }

    /**
     * Innermost of the middleware core registers, so the state is written before anything outside it sees
     * the result.
     */
    public static function priority(): int
    {
        return 100;
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        try {
            return $next($command, $context);
        } finally {
            // In finally rather than on success: a PSP token written before a later failure still has to
            // survive, or the retry opens a second checkout.
            $this->persist($command, $context);
        }
    }

    /**
     * @param CommandInterface<Result> $command
     */
    private function persist(CommandInterface $command, Context $context): void
    {
        $subject = $context->subject();
        $state = $context->pendingState();

        if (! $subject instanceof SubjectInterface || ! $state instanceof ArrayObject) {
            return;
        }

        $subject->setDetails($state);

        // Core writes back only what it loaded. A model handed to the command directly belongs to the
        // caller, who persists it on their own terms.
        if (! $command->token() instanceof TokenInterface || ! $this->storages instanceof StorageRegistryInterface) {
            return;
        }

        $this->storages->getStorage($subject::class)->update($subject);
    }
}
