<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\CreateSubscriptionCommand;
use Payum\Core\Event\NullEventDispatcher;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Handler\Context;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\SubscriptionInterface;
use Payum\Core\Reference\ReferenceIndexInterface;
use Payum\Core\Reference\ReferenceNamespace;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\NotifyResult;
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

        if (! $renewal instanceof Renewal) {
            return $result;
        }

        if (! $this->storages instanceof StorageRegistryInterface || ! $this->payments instanceof RenewalPaymentFactoryInterface) {
            throw new LogicException('the handler reported a renewal but this gateway has no storage to record the renewal payment in; build it with PayumBuilder and give the payment class a storage');
        }

        // Without a name the renewal cannot be indexed, and every redelivery would record another payment.
        $gatewayName = $context->gatewayName()
            ?? throw new LogicException('the handler reported a renewal but this gateway is not registered under a name, so the renewal payment cannot be found again; register it with PayumBuilder::registerGateway()');

        $subscription = $context->subscription();

        if (! $subscription instanceof SubscriptionInterface) {
            throw new LogicException('the handler reported a renewal but the command carries no subscription to record it on; dispatch it with a Subscription or a webhook that names one');
        }

        $payment = $this->recorded($context, $renewal) ?? $this->payments->create($subscription, $renewal);
        $storage = $this->storages->getStorage($payment);

        $change = PaymentStatusChange::record($payment, $renewal->status);
        $storage->update($payment);

        SubjectIdentity::record($this->references, $this->storages, $context, $payment, $renewal->transactionId, $gatewayName);

        $change?->announce($this->events, $command, $context, $renewal->failure, $command instanceof CreateSubscriptionCommand || $renewal->isFirst);

        return $result;
    }

    private function recorded(Context $context, Renewal $renewal): ?PaymentInterface
    {
        $record = $this->references->find(ReferenceNamespace::of($context), $renewal->transactionId);

        if (null === $record) {
            return null;
        }

        $identity = $record->getIdentity();

        try {
            $payment = $this->storages?->getStorage($identity->getClass())->find($identity);
        } catch (InvalidArgumentException) {
            return null;
        }

        return $payment instanceof PaymentInterface ? $payment : null;
    }

}
