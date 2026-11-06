# Sensitive information

Card numbers, security codes and card holder names must never end up in your database, your logs or your
error reports. If you store them, even for a moment, the
[PCI Data Security Standard](https://www.pcisecuritystandards.org/) applies to your whole application, and
meeting it is a large project of its own. Payum gives you three tools to pass card data to a payment
provider without keeping it:

- `Payum\Core\Model\CreditCard` holds the card the customer typed in, for the length of one request.
- `Payum\Core\Security\SensitiveValue` wraps a single value so it cannot be stored, serialized, logged or
  printed by accident.
- `Payum\Core\Security\Util\Mask` turns a card number into something you can safely store and show, such as
  `4XXXXXXXXXXX1111`.

The simplest protection is to never receive card data at all. Prefer a gateway that collects the card on
the provider's own page or in the provider's own form widget. Your server then only sees a token that
stands for the card, and you can skip this page.

## Take a card on your own page

Some gateways need you to collect the card yourself and pass it to them. Do it in one request: the form
posts the card to your code, your code captures the payment there and then, and the card is gone when the
request ends.

```php
<?php

use App\Entity\Payment;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Model\CreditCard;

/** @var Payment $payment the order's payment, loaded from your storage */
$form = $request->getParsedBody();

$card = new CreditCard();
$card->setHolder($form['holder']);
$card->setNumber($form['number']);
$card->setSecurityCode($form['cvc']);
$card->setExpireAt(new DateTime($form['expiry_year'] . '-' . $form['expiry_month'] . '-01'));

$payment->setCreditCard($card);

$result = $payum->getGateway('acme')->execute(CaptureCommand::forPayment($payment));

$payment->setCardMaskedNumber($card->getMaskedNumber());
$payment->setCreditCard(null);

$payum->getStorage(Payment::class)->update($payment);
```

What happens to the card on the way:

1. `CreditCard`'s setters wrap the number, holder, security code and expiry date in `SensitiveValue`, and
   keep a masked copy of the number and the holder.
2. The gateway reads the card from the payment, sends it to the provider and records what the provider
   returned, such as a charge id, on the payment's details.
3. You copy the masked number onto the payment, so you can show it later, and detach the card.
4. The payment is saved. Payum's Doctrine mapping has no column for the card, and every card value the
   gateway put in the details is stored as `null`.

After the capture the payment's details look like this in the database:

```json
{"card_number": null, "card_cvc": null, "card_expiry": null, "charge_id": "ch_1"}
```

`$result` tells you what happened, and whether the customer still has something to do, such as confirm
the payment with their bank. See [Payment operations](payment-operations.md) for acting on a result.

{% hint style="warning" %}
Use the card in the request that received it. A serialized `CreditCard` keeps only its masked copies, its
brand and its token, so a payment read back from a session, a cache, a message queue or a file has no card
number, holder, security code or expiry date to send.
{% endhint %}

Never attach the card before a redirect. A card set on a payment is not saved, so it is lost when the
customer leaves the page, and it must never be saved anywhere else to survive the trip.

## Store what you can show later

Customers and support staff need to recognise a card without seeing it. Store the masked number, and the
brand if your form or the provider gives you one. Add the columns to your own payment entity:

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Payment as BasePayment;

#[ORM\Entity]
#[ORM\Table(name: 'payment')]
class Payment extends BasePayment
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    protected $id;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $cardMaskedNumber = null;

    public function getCardMaskedNumber(): ?string
    {
        return $this->cardMaskedNumber;
    }

    public function setCardMaskedNumber(?string $cardMaskedNumber): void
    {
        $this->cardMaskedNumber = $cardMaskedNumber;
    }
}
```

`CreditCard::getMaskedNumber()` keeps the first digit and the last four: `4XXXXXXXXXXX1111`. To mask any
other value you show, such as a bank account number, call `Mask::mask()` yourself:

```php
use Payum\Core\Security\Util\Mask;

echo Mask::mask('DE89370400440532013000');
// DXXXXXXXXXXXXXXXXX3000
```

See [Mask a value](#mask-a-value) for the options. [Use your own payment model](your-order-integration.md)
covers the rest of the entity.

## Pass other sensitive values to a gateway

A gateway may document a details key for a value that is not a card, such as a bank account number for a
direct debit. Wrap the value in `SensitiveValue` before you put it on the payment:

```php
use Payum\Core\Security\SensitiveValue;

$payment->setDetails([
    'iban' => new SensitiveValue($form['iban']),
]);
```

The gateway unwraps it only to send it to the provider, and it is stored as `null`, like the card fields
above. Use the key the gateway documents: a value under a key the gateway does not read is never sent.

## Keep sensitive values out of logs

A `SensitiveValue` hides its value from everything that could write it somewhere:

```php
<?php

use Payum\Core\Security\SensitiveValue;

$number = new SensitiveValue('4111111111111111');

serialize($number);   // O:34:"Payum\Core\Security\SensitiveValue":0:{}, the value is not included
json_encode($number); // null
(string) $number;     // an empty string
print_r($number);     // shows the masked value 4XXXXXXXXXXX1111
clone $number;        // throws Payum\Core\Exception\LogicException
```

So dumping a payment's details while debugging, JSON-encoding them for a log, or including them in an
error report does not reveal a wrapped value. The getters of a `CreditCard`, such as `getNumber()`, return
the raw value: log `getMaskedNumber()` instead.

Never log the array a gateway builds with `toUnsafeArray()`, or the request it sends to the provider: those
hold the raw values.

## In a gateway: send sensitive values to the provider

A gateway that takes card data reads the card from the payment, puts each value into its state wrapped in
`SensitiveValue`, and unwraps them only to build the request to the provider:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum\Handler;

use Acme\Payum\AcmeApi;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Security\SensitiveValue;

final class CaptureHandler implements CaptureHandlerInterface
{
    public function __construct(
        private readonly AcmeApi $api,
    ) {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        $state = $context->state();
        $card = $context->payment()?->getCreditCard();

        if (null !== $card) {
            $state['card_number'] = SensitiveValue::ensureSensitive($card->getNumber());
            $state['card_cvc'] = SensitiveValue::ensureSensitive($card->getSecurityCode());
            $state['card_expiry'] = SensitiveValue::ensureSensitive($card->getExpireAt()?->format('m/y'));
        }

        $charge = $this->api->charge($state->toUnsafeArray());

        $state['charge_id'] = $charge['id'];

        return CaptureResult::captured($charge['id']);
    }
}
```

Here `AcmeApi::charge()` posts the fields to the provider and returns its response. Three things to get
right:

- `toUnsafeArray()` unwraps each `SensitiveValue` with `get()`, which erases it. Call it once per request
  to the provider. A second call returns `null` for every card field.
- The card is only there on the request where the application attached it. If your capture runs again
  when the customer comes back from the provider, the card is gone, so send it on the first run.
- `$context->state()` is saved onto the payment after the handler returns, with every `SensitiveValue`
  stored as `null`. Write anything you need on the next run, such as the charge id, as a plain value.

See [Handlers](gateways/handlers.md) for the rest of a handler.

## Reference

### SensitiveValue

| Method | What it does |
| :--- | :--- |
| `new SensitiveValue($value)` | Wraps a value |
| `SensitiveValue::ensureSensitive($value)` | Wraps `$value`, or returns it as it is when it is already wrapped |
| `peek()` | Returns the value and keeps it |
| `get()` | Returns the value and erases it, so it can be read only once |
| `erase()` | Erases the value |

After `get()` or `erase()`, `peek()` returns `null`.

### Mask a value

`Mask::mask()` keeps the first character and the end of the value, replaces the rest with `X`, and leaves
separators such as `-`, `_` and spaces in place:

```php
<?php

use Payum\Core\Security\Util\Mask;

echo Mask::mask('3456-7890-1234-5678');
// 3XXX-XXXX-XXXX-5678

echo Mask::mask('4567890123456789', '*');
// 4***********6789

echo Mask::mask('4928-9012-abcd-3456', null, 8);
// 4XXX-XXXX-abcd-3456
```

The second argument is the mask symbol, `X` when you pass `null`. The third controls how much of the end
stays visible. The mask leaves that number plus one characters, so the default of `3` leaves four. With
the default, a value of eight characters or fewer keeps only its first character.

### CreditCard

| Method | Returns |
| :--- | :--- |
| `setNumber()`, `setHolder()`, `setSecurityCode()`, `setExpireAt()` | Store the value wrapped in `SensitiveValue` |
| `getNumber()`, `getHolder()`, `getSecurityCode()`, `getExpireAt()` | The raw value, for the gateway to send |
| `getMaskedNumber()`, `getMaskedHolder()` | The masked copies, safe to store and show |
| `setBrand()`, `getBrand()` | The card brand, such as `visa`, if you know it |
| `setToken()`, `getToken()` | A token the provider issued for the card, if the gateway uses one |

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
