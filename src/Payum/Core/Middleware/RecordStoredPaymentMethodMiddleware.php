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
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\Result;
use Payum\Core\Result\StoredPaymentMethodDetails;
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

        $gatewayName = $context->gatewayName();
        $customer = $this->customerToUpdate($method->getCustomer() ?? $context->customer(), $gatewayName, $details->gatewayCustomerId);

        // Both storages are resolved before either save, so a missing one fails before the method is
        // changed or saved and a redelivery still sees the status change.
        $methodStorage = $this->storages?->getStorage($method);
        $customerStorage = $customer instanceof CustomerInterface ? $this->storages?->getStorage($customer) : null;

        $from = $method->getStatus();
        $method->apply($details);
        $methodStorage?->update($method);

        if ($customer instanceof CustomerInterface) {
            $customer->setGatewayCustomerId($gatewayName, $details->gatewayCustomerId);
            $customerStorage?->update($customer);
        }

        if (null !== $gatewayName) {
            SubjectIdentity::record($this->references, $this->storages, $context, $method, $method->getReference(), $gatewayName);
        }

        if ($from !== $method->getStatus()) {
            $this->events->dispatch(new PaymentMethodStatusChanged($command, $context, $method, $from, $method->getStatus()));
        }

        return $result;
    }

    /**
     * The customer whose gateway id the details change, or null when there is nothing to write.
     */
    private function customerToUpdate(?CustomerInterface $customer, ?string $gatewayName, ?string $id): ?CustomerInterface
    {
        if (! $customer instanceof CustomerInterface || null === $gatewayName || null === $id || $customer->getGatewayCustomerId($gatewayName) === $id) {
            return null;
        }

        return $customer;
    }
}
