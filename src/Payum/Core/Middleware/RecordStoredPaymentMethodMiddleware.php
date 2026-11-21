<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Command\CommandInterface;
use Payum\Core\Event\NullEventDispatcher;
use Payum\Core\Event\PaymentMethodStatusChanged;
use Payum\Core\Exception\LogicException;
use Payum\Core\Handler\Context;
use Payum\Core\Model\CustomerInterface;
use Payum\Core\Model\StoredPaymentMethodInterface;
use Payum\Core\Reference\ReferenceIndexInterface;
use Payum\Core\Reference\ReferenceNamespace;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\Result;
use Payum\Core\Result\StoredPaymentMethodDetails;
use Payum\Core\Storage\IdentityInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Commits what a handler reported the PSP stored onto the stored payment method and its customer, saves
 * both, and indexes the method's reference so a later webhook naming it finds the method.
 *
 * Core saves these even when the payment was handed in by the caller: the caller never saw the change.
 */
final class RecordStoredPaymentMethodMiddleware implements MiddlewareInterface, HasPriority
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
     * Inside IndexReferencesMiddleware and outside RecordPaymentStatusMiddleware.
     */
    public static function priority(): int
    {
        return 60;
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        $result = $next($command, $context);

        $details = $result->storedPaymentMethod();

        if (! $details instanceof StoredPaymentMethodDetails) {
            return $result;
        }

        $method = $context->storedPaymentMethod();

        if (! $method instanceof StoredPaymentMethodInterface) {
            throw new LogicException('the handler reported a stored payment method but the payment carries none to record it on; give the payment a StoredPaymentMethod before dispatching');
        }

        $from = $method->getStatus();
        $method->apply($details);
        $this->save($method);

        $gatewayName = $context->gatewayName();

        if (null !== $gatewayName) {
            $this->recordGatewayCustomerId($method->getCustomer() ?? $context->customer(), $gatewayName, $details->gatewayCustomerId);
            $this->index($context, $method, $gatewayName);
        }

        if ($from !== $method->getStatus()) {
            $this->events->dispatch(new PaymentMethodStatusChanged($command, $context, $method, $from, $method->getStatus()));
        }

        return $result;
    }

    private function recordGatewayCustomerId(?CustomerInterface $customer, string $gatewayName, ?string $id): void
    {
        if (! $customer instanceof CustomerInterface || null === $id || $customer->getGatewayCustomerId($gatewayName) === $id) {
            return;
        }

        $customer->setGatewayCustomerId($gatewayName, $id);
        $this->save($customer);
    }

    private function index(Context $context, StoredPaymentMethodInterface $method, string $gatewayName): void
    {
        $reference = $method->getReference();

        if (null === $reference || '' === $reference) {
            return;
        }

        $identity = SubjectIdentity::identityOf($method, $this->storages);

        if ($identity instanceof IdentityInterface && null !== SubjectIdentity::key($identity)) {
            $this->references->record(ReferenceNamespace::of($context), $reference, $gatewayName, $identity);
        }
    }

    private function save(object $model): void
    {
        $this->storages?->getStorage($model)->update($model);
    }
}
