<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Event\NullEventDispatcher;
use Payum\Core\Event\StatusChanged;
use Payum\Core\Exception\LogicException;
use Payum\Core\Handler\Context;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\PaymentStatuses;
use Payum\Core\Model\SubscriptionInterface;
use Payum\Core\Reference\ReferenceIndexInterface;
use Payum\Core\Reference\ReferenceNamespace;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\NotifyResult;
use Payum\Core\Result\PaymentStatus;
use Payum\Core\Result\Renewal;
use Payum\Core\Result\Result;
use Payum\Core\Result\SubscriptionResult;
use Payum\Core\Subscription\RenewalPaymentFactoryInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Records a charge the PSP took on a subscription as a payment of that subscription, one per PSP
 * transaction: a redelivered webhook, or a paid retry of a failed charge, updates the payment the first
 * report created.
 *
 * The renewal payment is not the command's subject, so this sets and saves its status itself.
 */
final class RecordRenewalMiddleware implements MiddlewareInterface, HasPriority
{
    /**
     * @param StorageRegistryInterface<object>|null $storages
     */
    public function __construct(
        private readonly ReferenceIndexInterface $references,
        private readonly ?StorageRegistryInterface $storages = null,
        private readonly ?RenewalPaymentFactoryInterface $payments = null,
        private readonly EventDispatcherInterface $events = new NullEventDispatcher(),
    ) {
    }

    /**
     * Inside IndexReferencesMiddleware and outside RecordSubscriptionMiddleware.
     */
    public static function priority(): int
    {
        return 70;
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $result = $next($command, $context);

        $renewal = $result instanceof SubscriptionResult || $result instanceof NotifyResult ? $result->renewal() : null;
        $gatewayName = $context->gatewayName();

        // Without a storage or a gateway name the payment could be neither saved nor found again, and every
        // redelivery would make another one.
        if (! $renewal instanceof Renewal || ! $this->storages instanceof StorageRegistryInterface || ! $this->payments instanceof RenewalPaymentFactoryInterface || null === $gatewayName) {
            return $result;
        }

        $subscription = $context->subscription();

        if (! $subscription instanceof SubscriptionInterface) {
            throw new LogicException('the handler reported a renewal but the command carries no subscription to record it on; dispatch it with a Subscription or a webhook that names one');
        }

        $payment = $this->recorded($context, $renewal) ?? $this->payments->create($subscription, $renewal);
        $storage = $this->storages->getStorage($payment);

        $from = PaymentStatuses::of($payment);
        $moved = PaymentStatuses::set($payment, $renewal->status) && $from !== $renewal->status;
        $storage->update($payment);

        SubjectIdentity::record($this->references, $this->storages, $context, $payment, $renewal->transactionId, $gatewayName);

        if ($moved) {
            $this->statusChanged($command, $context, $payment, $from, $renewal->status);
        }

        return $result;
    }

    private function recorded(Context $context, Renewal $renewal): ?PaymentInterface
    {
        $record = $this->references->find(ReferenceNamespace::of($context), $renewal->transactionId);

        if (null === $record) {
            return null;
        }

        $identity = $record->getIdentity();
        $payment = $this->storages?->getStorage($identity->getClass())->find($identity);

        return $payment instanceof PaymentInterface ? $payment : null;
    }

    /**
     * @param CommandInterface<Result> $command
     */
    private function statusChanged(CommandInterface $command, Context $context, PaymentInterface $payment, ?PaymentStatus $from, PaymentStatus $to): void
    {
        $this->events->dispatch(new StatusChanged($command, $context, $payment, $from, $to));
    }
}
