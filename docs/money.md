# Money

Payum expresses amounts as [`moneyphp/money`](https://moneyphp.org) objects. A `Money\Money` is an exact
integer amount plus a currency, so it adds, subtracts and splits without ever turning into a float — which
is what you need to say "refund half of this" and get the last cent right.

Nothing forces you to use it. A model that carries an integer and a currency code keeps working exactly as
it did, and so does every gateway. Adding it to your own code is opt-in, one place at a time.

### The problem it solves

An integer on its own is not an amount. `1000` is ten dollars, ten yen, or one dinar, depending on a
currency code stored somewhere else — and code that divides by 100 to display it is wrong for both of the
other two:

```php
$payment->getTotalAmount() / 100;   // 10.00 for USD. Wrong for JPY, wrong for KWD, wrong for ETH.
```

A `Money` carries the currency, and Payum knows how many decimal places that currency has, so there is
nothing left to get wrong.

## Reading an amount

Every model Payum ships answers `getMoney()`:

```php
<?php
use Payum\Core\Model\Payment;
use Money\Money;

$payment = new Payment();
$payment->setMoney(Money::USD(1000));

$payment->getMoney();          // Money, 1000 USD
$payment->getTotalAmount();    // 1000
$payment->getCurrencyCode();   // 'USD'
```

`setMoney()` writes through to `totalAmount` and `currencyCode`, and `getMoney()` reads back from them, so
the two views can never disagree and **no mapping or database column changes**. A `Money` needs no storage
of its own: your `total_amount` and `currency_code` columns already hold everything it is made of.

Your own entity opts in by implementing `Payum\Core\Model\MoneyAwareInterface` alongside
`PaymentInterface`:

```php
<?php
use Money\Money;
use Payum\Core\Model\MoneyAwareInterface;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Money\Amount;

class Order implements PaymentInterface, MoneyAwareInterface
{
    private ?int $totalAmount = null;

    private ?string $currencyCode = null;

    public function getMoney(): ?Money
    {
        return Amount::fromMinorUnits($this->totalAmount, $this->currencyCode);
    }

    public function setMoney(?Money $money): void
    {
        $this->totalAmount = $money === null ? null : Amount::toMinorUnits($money);
        $this->currencyCode = $money?->getCurrency()->getCode();
    }

    // getTotalAmount(), getCurrencyCode() and the rest of PaymentInterface as before
}
```

Do not implement `MoneyAwareInterface` and store a `Money` field instead of the two columns. Every
gateway Payum ships reads `getTotalAmount()` and `getCurrencyCode()`, so those have to keep answering.

### When you do not control the model

`Payum\Core\Money\Amount` reads either shape, which is what to use in code that is handed a model it did
not define:

```php
<?php
use Payum\Core\Money\Amount;

Amount::of($payment);              // ?Money, from getMoney() or from the two accessors
Amount::assign($payment, $money);  // writes through whichever the model has
```

`Amount::of()` returns null when the model names no currency or holds no amount — an amount without a
currency is not money.

## Amounts on a command

A partial capture or refund takes either minor units or a `Money`:

```php
<?php
use Money\Money;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Money\Amount;

CaptureCommand::forToken($token, Money::USD(500));   // exact, currency included
CaptureCommand::forToken($token, 500);               // minor units of the payment's currency
CaptureCommand::forToken($token);                    // the payment's full amount

RefundCommand::forPayment($payment, Amount::of($payment)->divide(2), 'partially_damaged');
```

Both forms are readable afterwards:

```php
$command->amount;    // ?int, minor units
$command->money();   // ?Money, when the command carries enough to say
```

## Amounts in a handler

Read `$context->amount()`, not `$command->amount`:

```php
public function handle(CaptureCommand $command, Context $context): CaptureResult
{
    $amount = $context->amount();   // Money — the partial amount, or the payment's full amount
}
```

The distinction matters: a **full** capture carries no amount on the command at all, so a handler reading
`$command->amount` charges nothing. `$context->amount()` resolves the two, and works out the currency for
a command that arrived on a token alone and so has no payment of its own to read one from.

### Sending it to the PSP

Most PSPs want either minor units or a decimal string. Both come out of the `Money`, and a handler gets a
`Money\MoneyFormatter` by asking for one in its constructor:

```php
<?php
use Money\MoneyFormatter;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\CaptureResult;

final class CaptureHandler implements CaptureHandlerInterface
{
    public function __construct(
        private readonly AcmeApi $api,
        private readonly MoneyFormatter $formatter,
    ) {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        $amount = $context->amount();

        $charge = $this->api->charge([
            'amount' => $amount->getAmount(),                 // '1000', minor units, as a string
            'decimal' => $this->formatter->format($amount),   // '10.00', or '1000' for JPY
            'currency' => $amount->getCurrency()->getCode(),  // 'USD'
        ]);

        return CaptureResult::captured($charge['id'], $amount);
    }
}
```

## Amounts on a result

Report what actually moved, as a `Money`:

```php
CaptureResult::captured($transactionId, Money::USD(500));
RefundResult::partiallyRefunded($transactionId, $refunded);
PayoutResult::paidOut($transactionId, $sent);
```

Each result exposes both views, so an application reading integers is unaffected:

```php
$result->capturedMoney;    // ?Money, set when the handler reported one
$result->capturedAmount;   // ?int, minor units
```

The same pair exists as `authorizedMoney`/`authorizedAmount`, `refundedMoney`/`refundedAmount` and
`paidOutMoney`/`paidOutAmount`.

## Currencies

Payum resolves how many decimal places a currency has through a `Money\Currencies` service. Out of the box
it knows every ISO 4217 currency and the crypto currencies moneyphp lists, ISO first:

```php
$currencies->subunitFor(new Currency('USD'));   // 2
$currencies->subunitFor(new Currency('JPY'));   // 0
$currencies->subunitFor(new Currency('KWD'));   // 3
$currencies->subunitFor(new Currency('BTC'));   // 8
```

Register your own to add a currency neither lists — a token, a loyalty point, a chain moneyphp has not
caught up with:

```php
<?php
use Money\Currencies;
use Money\Currencies\AggregateCurrencies;
use Money\Currencies\CurrencyList;
use Money\Currencies\ISOCurrencies;
use Payum\Core\PayumBuilder;

$payum = (new PayumBuilder())
    ->addGlobalService(Currencies::class, new AggregateCurrencies([
        new ISOCurrencies(),
        new CurrencyList(['ACME' => 6]),
    ]))
    ->getPayum()
;
```

Order matters: the first list containing a code answers for it, so keep `ISOCurrencies` first unless you
mean to override a real currency.

`Money\MoneyFormatter` and `Money\MoneyParser` are registered against whatever `Currencies` resolves to,
so replacing it is enough — nothing else needs redeclaring.

## Currency details in a 1.x gateway

A gateway that has not been ported still asks for currency details with a `GetCurrency` request, and that
keeps working. It now also answers for a currency ISO 4217 does not list, taking the exponent from the
`Currencies` service — so registering a currency there is what makes an unported gateway handle it too:

```php
$gateway->execute($currency = new GetCurrency('BTC'));

$currency->exp;   // 8
```

A currency outside ISO 4217 has no numeric code or country, and its name comes back as the code itself —
`exp` and `alpha3` are the useful part.

New code should not reach for `GetCurrency` or `Payum\Core\ISO4217\Currency`. A handler takes a
`Money\Currencies` or a `Money\MoneyFormatter` in its constructor and asks it directly.

### Next

* [Commands](gateways/commands.md)
* [Handlers](gateways/handlers.md)
* [Results](gateways/results.md)

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
