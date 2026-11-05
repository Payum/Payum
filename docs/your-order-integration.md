# Use your own payment model

Payum ships a `Payum\Core\Model\Payment` class, but your application probably has its own order or
payment entity already. Payum works with any class that implements its payment interfaces and has a
storage registered for it. Gateways only read the payment through those interfaces, so they work with
your class unchanged.

## Extend Payum's payment

The quickest way is to extend `Payum\Core\Model\Payment` and add what your application needs. This
sample adds an id for Doctrine and the status field Payum keeps current:

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Payment as BasePayment;
use Payum\Core\Model\StatusAwareInterface;
use Payum\Core\Result\PaymentStatus;

#[ORM\Entity]
#[ORM\Table]
class Payment extends BasePayment implements StatusAwareInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    #[ORM\Column(type: 'string', enumType: PaymentStatus::class)]
    protected PaymentStatus $status = PaymentStatus::New;

    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    public function setStatus(PaymentStatus $status): void
    {
        $this->status = $status;
    }
}
```

The parent class brings the number, description, client fields, amount, currency and the gateway
details, and Payum ships a Doctrine mapping for them. [Storages](storages.md) shows how to load that
mapping next to your own.

Implementing `StatusAwareInterface` is optional, but without it Payum cannot record the status, and
`PaymentStatuses::of()` returns `null` for your payments. [Payment status](payment-status.md) explains
what Payum records and when.

## Implement the interfaces yourself

When your order class cannot extend Payum's, implement the interfaces directly. A payment class needs
`Payum\Core\Model\PaymentInterface`, and two optional interfaces add more:

| Interface | Methods | Why |
| :--- | :--- | :--- |
| `PaymentInterface` | `getNumber()`, `getDescription()`, `getClientEmail()`, `getClientId()`, `getTotalAmount()`, `getCurrencyCode()`, `getCreditCard()`, `getDetails()`, `setDetails()` | Required. Gateways read the payment through it. |
| `StatusAwareInterface` | `getStatus()`, `setStatus()` | Payum records the payment's status. |
| `MoneyAwareInterface` | `getMoney()`, `setMoney()` | Your code and Payum read the amount as a `Money\Money`. |

Here is an order that implements all three:

```php
<?php

namespace App\Entity;

use Money\Money;
use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Model\MoneyAwareInterface;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\StatusAwareInterface;
use Payum\Core\Money\Amount;
use Payum\Core\Result\PaymentStatus;

class Order implements PaymentInterface, MoneyAwareInterface, StatusAwareInterface
{
    private string $number;

    private ?int $totalAmount = null;

    private ?string $currencyCode = null;

    /**
     * @var array<string, mixed>
     */
    private array $details = [];

    private PaymentStatus $status = PaymentStatus::New;

    public function __construct(
        string $number,
        private readonly string $customerEmail,
        private readonly string $customerId,
    ) {
        $this->number = $number;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getDescription(): string
    {
        return 'Order '.$this->number;
    }

    public function getClientEmail(): string
    {
        return $this->customerEmail;
    }

    public function getClientId(): string
    {
        return $this->customerId;
    }

    public function getTotalAmount(): ?int
    {
        return $this->totalAmount;
    }

    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }

    public function getMoney(): ?Money
    {
        return Amount::fromMinorUnits($this->totalAmount, $this->currencyCode);
    }

    public function setMoney(?Money $money): void
    {
        $this->totalAmount = null === $money ? null : Amount::toMinorUnits($money);
        $this->currencyCode = $money?->getCurrency()->getCode();
    }

    public function getCreditCard(): ?CreditCardInterface
    {
        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param iterable<string, mixed> $details
     */
    public function setDetails($details): void
    {
        $this->details = is_array($details) ? $details : iterator_to_array($details);
    }

    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    public function setStatus(PaymentStatus $status): void
    {
        $this->status = $status;
    }
}
```

Keep these points in mind when you write your own:

- `getTotalAmount()` returns minor units, so `1000` with `'EUR'` is 10.00 EUR. See
  [Money and currencies](money.md).
- `getDetails()` and `setDetails()` hold the gateway's own data: the provider's ids and what it has
  answered so far. Store them as they are, for example in a JSON column, and do not edit them. Payum
  passes `setDetails()` an object that can be iterated, not a plain array, so convert it as the sample
  does.
- `getCreditCard()` returns `null` unless you collect card data yourself, which most applications should
  not do. See [Sensitive information](working-with-sensitive-information.md).
- Start the status at `PaymentStatus::New`.

## Register a storage for it

Payum loads and saves payments through a storage registered for their class. Register one for yours:

```php
<?php

use Acme\Payum\AcmeConfig;
use App\Entity\Order;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\PayumBuilder;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addStorage(Order::class, new DoctrineStorage($entityManager, Order::class))
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

Map `Order` as a Doctrine entity first; the class above leaves the mapping out to stay short. Payum
finds the storage by the payment's exact class, so register the class you actually save. A storage
registered for a parent class does not cover its subclasses. [Storages](storages.md) lists the storages
Payum ships.

## Take a payment with it

Your class now works everywhere Payum's `Payment` does:

```php
<?php

use App\Entity\Order;
use Money\Money;

$order = new Order('1001', 'customer@example.com', 'customer-42');
$order->setMoney(Money::EUR(1000));

$token = $payum->prepare('acme', $order);

header('Location: '.$token->getTargetUrl());
```

`prepare()` saves the order in its storage and returns the capture token. From here the capture and done
scripts in [Payment operations](payment-operations.md) work as they are.

## Payouts

A payout class works the same way with `Payum\Core\Model\PayoutInterface`. It has `getRecipientId()`,
`getRecipientEmail()`, `getDescription()`, `getTotalAmount()`, `getCurrencyCode()`, `getDetails()` and
`setDetails()`. Extend `Payum\Core\Model\Payout` or implement the interface, add `StatusAwareInterface`
to track its status, and register a storage for the class.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
