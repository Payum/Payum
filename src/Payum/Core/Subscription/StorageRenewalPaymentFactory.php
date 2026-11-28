<?php

declare(strict_types=1);

namespace Payum\Core\Subscription;

use Payum\Core\Exception\LogicException;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\SubscriptionInterface;
use Payum\Core\Money\Amount;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Result\Renewal;
use function method_exists;
use function sprintf;

/**
 * Creates the renewal payment through the storage of a payment class, filled from the subscription.
 *
 * The number is the PSP's transaction id: a renewal has no order number of its own, and that id is unique
 * and stays the same however often the PSP reports the charge.
 */
final class StorageRenewalPaymentFactory implements RenewalPaymentFactoryInterface
{
    /**
     * @param StorageRegistryInterface<object> $storages
     * @param class-string<PaymentInterface> $paymentClass
     */
    public function __construct(
        private readonly StorageRegistryInterface $storages,
        private readonly string $paymentClass = Payment::class,
    ) {
    }

    public function create(SubscriptionInterface $subscription, Renewal $renewal): PaymentInterface
    {
        $payment = $this->storages->getStorage($this->paymentClass)->create();

        if (! $payment instanceof PaymentInterface) {
            throw new LogicException(sprintf('The storage for %s created a %s, which is not a payment.', $this->paymentClass, $payment::class));
        }

        $customer = $subscription->getCustomer();

        Amount::assign($payment, $renewal->amount);
        self::set($payment, 'setNumber', $renewal->transactionId);
        self::set($payment, 'setDescription', $subscription->getDescription());
        self::set($payment, 'setClientId', $customer?->getCustomerId());
        self::set($payment, 'setClientEmail', $customer?->getEmail());
        self::set($payment, 'setSubscription', $subscription);
        self::set($payment, 'setCustomer', $customer);
        self::set($payment, 'setStoredPaymentMethod', $subscription->getStoredPaymentMethod());

        return $payment;
    }

    /**
     * The payment interfaces only read, so a field is written wherever the payment has a setter for it.
     */
    private static function set(object $payment, string $setter, mixed $value): void
    {
        if (null !== $value && method_exists($payment, $setter)) {
            $payment->{$setter}($value);
        }
    }
}
