<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Event\NullEventDispatcher;
use Payum\Core\Event\SubscriptionStatusChanged;
use Payum\Core\Exception\LogicException;
use Payum\Core\Handler\Context;
use Payum\Core\Model\SubscriptionInterface;
use Payum\Core\Reference\ReferenceIndexInterface;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\Result;
use Payum\Core\Result\SubscriptionDetails;
use Payum\Core\Result\SubscriptionResult;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Commits what a handler reported about a subscription onto it, saves it, and indexes its reference so a
 * later webhook naming it finds the subscription.
 */
final class RecordSubscriptionMiddleware implements MiddlewareInterface, HasPriority
{
    /**
     * @param StorageRegistryInterface<object>|null $storages
     */
    public function __construct(
        private readonly ReferenceIndexInterface $references,
        private readonly ?StorageRegistryInterface $storages = null,
        private readonly EventDispatcherInterface $events = new NullEventDispatcher(),
    ) {
    }

    /**
     * Inside IndexReferencesMiddleware and outside RecordStoredPaymentMethodMiddleware.
     */
    public static function priority(): int
    {
        return 65;
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $result = $next($command, $context);

        $details = $result instanceof SubscriptionResult || $result instanceof NotifyResult ? $result->subscription() : null;

        if (! $details instanceof SubscriptionDetails) {
            return $result;
        }

        $subscription = $context->subscription();

        if (! $subscription instanceof SubscriptionInterface) {
            throw new LogicException('the handler reported a subscription but the command carries none to record it on; dispatch it with a Subscription or a payment that belongs to one');
        }

        // Resolved before apply(), so a missing storage fails with nothing changed and a redelivery still
        // sees the status change.
        $storage = $this->storages?->getStorage($subscription);

        $from = $subscription->getStatus();
        $subscription->apply($details);
        $storage?->update($subscription);

        $gatewayName = $context->gatewayName();

        if (null !== $gatewayName) {
            SubjectIdentity::record($this->references, $this->storages, $context, $subscription, $subscription->getReference(), $gatewayName);
        }

        if ($from !== $subscription->getStatus()) {
            $this->events->dispatch(new SubscriptionStatusChanged($command, $context, $subscription, $from, $subscription->getStatus()));
        }

        return $result;
    }
}
