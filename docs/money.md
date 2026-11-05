# Money and currencies

An amount on its own is not money. `1000` is ten euros, ten yen or one Kuwaiti dinar depending on the
currency, and code that divides by 100 to display it is wrong for two of those three. Payum stores
amounts as integers in the currency's smallest unit, together with an ISO 4217 currency code, and lets
you work with them as [`moneyphp/money`](https://moneyphp.org) objects. A `Money\Money` is an exact
integer amount plus its currency, so it adds, subtracts and splits without rounding errors.

You do not have to use `Money`. Every place that takes one also takes an integer in minor units, and
every gateway keeps reading `getTotalAmount()` and `getCurrencyCode()`.

## Amounts are in minor units

Set an amount in the currency's smallest unit:

```php
<?php

use Payum\Core\Model\Payment;

$payment = new Payment();
$payment->setTotalAmount(1000);     // 10.00 EUR
$payment->setCurrencyCode('EUR');
```

A minor unit is the cent for euros and dollars. Some currencies have none: `1000` JPY is a thousand yen.
Others have three decimal places: `1000` KWD is one dinar. Never assume two decimal places. The next
sections show how to convert safely.

## Read and set an amount as a `Money`

`Payum\Core\Model\Payment` and `Payum\Core\Model\Payout` read and write their amount as a `Money`:

```php
<?php

use Money\Money;
use Payum\Core\Model\Payment;

$payment = new Payment();
$payment->setMoney(Money::USD(1000));

$payment->getMoney();          // Money, 1000 USD
$payment->getTotalAmount();    // 1000
$payment->getCurrencyCode();   // 'USD'
```

`setMoney()` writes to the same `totalAmount` and `currencyCode` fields that `setTotalAmount()` and
`setCurrencyCode()` use, so the two views always agree. A `Money` needs no database column of its own;
the two columns you already have hold all of it.

### Your own payment class

Implement `Payum\Core\Model\MoneyAwareInterface` next to `PaymentInterface`, and map it over the same two
fields:

```php
<?php

namespace App\Entity;

use Money\Money;
use Payum\Core\Model\MoneyAwareInterface;
use Payum\Core\Money\Amount;

class Order implements MoneyAwareInterface
{
    private ?int $totalAmount = null;

    private ?string $currencyCode = null;

    public function getMoney(): ?Money
    {
        return Amount::fromMinorUnits($this->totalAmount, $this->currencyCode);
    }

    public function setMoney(?Money $money): void
    {
        $this->totalAmount = null === $money ? null : Amount::toMinorUnits($money);
        $this->currencyCode = $money?->getCurrency()->getCode();
    }
}
```

The sample shows only the money part. A payment class also implements `PaymentInterface`, as
[Use your own payment model](your-order-integration.md) shows. Keep `getTotalAmount()` and
`getCurrencyCode()` answering from those fields. Gateways read them, so do not replace the two columns
with a single `Money` field.

### When you do not know the class

`Payum\Core\Money\Amount` reads and writes an amount through whichever accessors a model has:

```php
<?php

use Payum\Core\Money\Amount;

Amount::of($payment);              // ?Money, from getMoney() or from the two accessors
Amount::assign($payment, $money);  // writes through setMoney() or the two setters
```

`Amount::of()` returns `null` when the model has no amount or no currency, because an amount without a
currency is not money.

## Amounts on commands and results

Capture, authorize and refund commands take an amount as a `Money` or as minor units of the payment's
currency. Leave it out for the payment's full amount:

```php
<?php

use Money\Money;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\RefundCommand;
use Payum\Core\Money\Amount;

CaptureCommand::forPayment($payment, Money::EUR(500));   // exact, currency included
CaptureCommand::forPayment($payment, 500);               // minor units of the payment's currency
CaptureCommand::forPayment($payment);                    // the payment's full amount

RefundCommand::forPayment($payment, Amount::of($payment)->divide(2), 'partially_damaged');
```

A command keeps both forms: `$command->amount` is the integer and `$command->money()` the `Money`.

A result says how much actually moved, which can be less than you asked for:

```php
$result->capturedMoney;    // ?Money, set when the gateway reported one
$result->capturedAmount;   // ?int, minor units
```

The same pair is `authorizedMoney` and `authorizedAmount` on an `AuthorizeResult`, `refundedMoney` and
`refundedAmount` on a `RefundResult`, and `paidOutMoney` and `paidOutAmount` on a `PayoutResult`.
[Payment operations](payment-operations.md) shows each command in use.

## Display an amount

Turn minor units into a decimal string with `Amount::toDecimalString()`:

```php
<?php

use Payum\Core\Money\Amount;

Amount::toDecimalString(1000, 'EUR');   // '10.00'
Amount::toDecimalString(1000, 'JPY');   // '1000'
Amount::toDecimalString(1000, 'KWD');   // '1.000'
```

It looks the number of decimal places up in ISO 4217, so the result is right for every currency and
never goes through a float. For a currency ISO 4217 does not list, pass the number of decimal places as
the third argument: `Amount::toDecimalString(1, 'ACME', 6)` gives `'0.000001'`.

For anything beyond that, such as a currency symbol or a locale's separators, use a moneyphp formatter
on the payment's `Money`.

## Currency details

`Payum\Core\ISO4217\Currency` gives the ISO 4217 data for a currency:

```php
<?php

use Payum\Core\ISO4217\Currency;

$currency = Currency::createFromIso4217Alpha3('USD');

$currency->getName();      // 'US Dollar'
$currency->getAlpha3();    // 'USD'
$currency->getNumeric();   // '840'
$currency->getExp();       // 2, the number of decimal places
$currency->getCountry();   // the ISO 3166 codes of the countries that use it

Currency::createFromIso4217Numeric('978')->getAlpha3();   // 'EUR'
```

It knows only ISO 4217 currencies and throws for any other code, such as `BTC`.

## Currencies ISO 4217 does not list

Gateways look up how many decimal places a currency has through a `Money\Currencies` service. By
default it knows every ISO 4217 currency and the crypto currencies moneyphp lists, such as `BTC` with 8.
To add another, such as a token or loyalty points, register your own list as a global service:

```php
<?php

use Acme\Payum\AcmeConfig;
use Money\Currencies;
use Money\Currencies\AggregateCurrencies;
use Money\Currencies\CryptoCurrencies;
use Money\Currencies\CurrencyList;
use Money\Currencies\ISOCurrencies;
use Payum\Core\PayumBuilder;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGlobalService(Currencies::class, new AggregateCurrencies([
        new ISOCurrencies(),
        new CryptoCurrencies(),
        new CurrencyList(['ACME' => 6]),
    ]))
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

`CurrencyList` maps each code to its number of decimal places. The first list that contains a code
answers for it, so keep `ISOCurrencies` first unless you mean to override a real currency. Your list
replaces the default one, which is why the sample lists `CryptoCurrencies` again.

## Advanced: amounts in a gateway handler

If you write a gateway, read the amount of the current operation with `$context->amount()`, not
`$command->amount`. A command for the full amount carries no amount at all, so a handler that reads
`$command->amount` charges nothing; `$context->amount()` falls back to the payment's total and works out
the currency. A handler gets a `Money\MoneyFormatter` or `Money\Currencies` by asking for one in its
constructor, and both follow the `Currencies` service you registered. See [Handlers](gateways/handlers.md).

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
