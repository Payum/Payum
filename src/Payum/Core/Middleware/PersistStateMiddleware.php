<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Model\StatusAwareInterface;
use Payum\Core\Model\SubjectInterface;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Result;
use Payum\Core\Security\TokenInterface;

/**
 * Writes the PSP state a handler produced back onto the payment, and saves a payment loaded from a token
 * when its state or its status changed.
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
     * Outside the middleware that mutate the payment, so their changes are included in the write.
     */
    public static function priority(): int
    {
        return 100;
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $statusBefore = $this->statusOf($context->subject());

        try {
            return $next($command, $context);
        } finally {
            // In finally rather than on success: a PSP token written before a later failure still has to
            // survive, or the retry opens a second checkout.
            $this->persist($command, $context, $statusBefore);
        }
    }

    /**
     * @param CommandInterface<Result> $command
     */
    private function persist(CommandInterface $command, Context $context, ?PaymentStatus $statusBefore): void
    {
        $subject = $context->subject();

        if (! $subject instanceof SubjectInterface) {
            return;
        }

        $state = $context->pendingState();

        if ($state instanceof ArrayObject) {
            $subject->setDetails($state);
        } elseif ($this->statusOf($subject) === $statusBefore) {
            return;
        }

        // Core writes back only what it loaded. A model handed to the command directly belongs to the
        // caller, who persists it on their own terms.
        if (! $command->token() instanceof TokenInterface || ! $this->storages instanceof StorageRegistryInterface) {
            return;
        }

        $this->storages->getStorage($subject::class)->update($subject);
    }

    private function statusOf(?SubjectInterface $subject): ?PaymentStatus
    {
        return $subject instanceof StatusAwareInterface ? $subject->getStatus() : null;
    }
}
