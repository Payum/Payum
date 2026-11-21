<?php

declare(strict_types=1);

namespace Payum\Core\Middleware;

use Payum\Core\Command\AuthorizeCommand;
use Payum\Core\Command\CancelSubscriptionCommand;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\CommandInterface;
use Payum\Core\Command\CreateSubscriptionCommand;
use Payum\Core\Command\Initiator;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\UnsupportedOperationException;
use Payum\Core\Gateway\Capability;
use Payum\Core\Gateway\DeclaresCapabilities;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\StoredCredential;
use Payum\Core\Model\SubscriptionSchedule;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\Result;
use function in_array;
use function sprintf;

/**
 * Refuses a charge on a stored payment method, or a subscription operation, that the gateway has not
 * declared it can make. A gateway that declares no capabilities at all, as every 1.x one, is let through.
 */
final class CapabilityGuardMiddleware implements MiddlewareInterface, HasPriority
{
    /**
     * @param StorageRegistryInterface<object>|null $storages identifies a payment handed to a command directly
     */
    public function __construct(
        private readonly ?StorageRegistryInterface $storages = null,
    ) {
    }

    /**
     * Must run under LockingMiddleware: reading the stored method loads the payment.
     */
    public static function priority(): int
    {
        return 850;
    }

    public function process(CommandInterface $command, Context $context, callable $next): Result
    {
        if ($command instanceof CreateSubscriptionCommand && SubscriptionSchedule::Application === $context->subscription()?->getSchedule()) {
            throw new LogicException(sprintf(
                '%s on gateway "%s" was given a subscription the application schedules, which the gateway does not create: an application-scheduled subscription is charged with CaptureCommand, one payment per period.',
                $command::class,
                $context->gatewayName() ?? $context->gateway()->name(),
            ));
        }

        if ($command instanceof CancelSubscriptionCommand && $command->atPeriodEnd && ! $this->declares($context, Capability::CancelAtPeriodEnd)) {
            throw new UnsupportedOperationException(sprintf(
                'Gateway "%s" does not declare Capability::CancelAtPeriodEnd, so it cannot cancel a subscription at the end of its period. Nothing was sent to the PSP.',
                $context->gatewayName() ?? $context->gateway()->name(),
            ));
        }

        $credential = $command instanceof CaptureCommand || $command instanceof AuthorizeCommand ? $context->storedCredential() : null;

        if (! $credential instanceof StoredCredential) {
            return $next($command, $context);
        }

        $gatewayName = $context->gatewayName() ?? $context->gateway()->name();

        if (null === SubjectIdentity::of($command, $this->storages)) {
            throw new LogicException(sprintf(
                '%s on gateway "%s" charges a stored payment method, but its payment has no storage identity: save the payment before dispatching a charge on a stored payment method.',
                $command::class,
                $gatewayName,
            ));
        }

        $gateway = $context->gateway();

        if (! $gateway instanceof DeclaresCapabilities) {
            return $next($command, $context);
        }

        $missing = match (true) {
            $credential->isFirst && ! in_array(Capability::StoredPaymentMethods, $gateway->capabilities(), true) => Capability::StoredPaymentMethods,
            Initiator::Merchant === $credential->initiator && ! in_array(Capability::Recurring, $gateway->capabilities(), true) => Capability::Recurring,
            default => null,
        };

        if ($missing instanceof Capability) {
            throw new UnsupportedOperationException(sprintf(
                'Gateway "%s" does not declare Capability::%s, so it cannot %s. Nothing was sent to the PSP.',
                $gatewayName,
                $missing->name,
                Capability::Recurring === $missing ? 'make a merchant-initiated charge on a stored payment method' : 'save a payment method during a charge',
            ));
        }

        return $next($command, $context);
    }

    private function declares(Context $context, Capability $capability): bool
    {
        $gateway = $context->gateway();

        return ! $gateway instanceof DeclaresCapabilities || in_array($capability, $gateway->capabilities(), true);
    }
}
