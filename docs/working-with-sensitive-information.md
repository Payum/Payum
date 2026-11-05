# Sensitive information

Card numbers, security codes and card holder names must never end up in your database, your logs or
your error reports. If you store them, even for a moment, the
[PCI Data Security Standard](https://www.pcisecuritystandards.org/) applies to your whole application,
and meeting it is a large project of its own. This page shows the tools Payum gives you to avoid storing
card data by accident.

The simplest protection is to never receive card data at all. Prefer a gateway that collects the card on
the payment provider's own page or in the provider's own form widget. Your server then only sees a token
that stands for the card.

## Wrap sensitive values

When card data has to pass through your code, wrap each value in `Payum\Core\Security\SensitiveValue`.
The wrapper hides the value from everything that could write it somewhere:

```php
<?php

use Payum\Core\Security\SensitiveValue;

$number = new SensitiveValue('4111111111111111');

serialize($number);   // O:34:"Payum\Core\Security\SensitiveValue":0:{}, the value is not included
json_encode($number); // null
(string) $number;     // an empty string
var_dump($number);    // shows the masked value 4XXXXXXXXXXX1111
clone $number;        // throws Payum\Core\Exception\LogicException
```

Read the value back only where you hand it to the provider:

| Method | What it does |
| :--- | :--- |
| `peek()` | Returns the value and keeps it |
| `get()` | Returns the value and erases it, so it can be read only once |
| `erase()` | Erases the value |
| `SensitiveValue::ensureSensitive($value)` | Wraps `$value`, or returns it as it is when it is already wrapped |

Use `get()` unless you need the value more than once. After `get()`, `peek()` returns `null`.

A gateway keeps the state of a payment in an `ArrayObject` that leaves `SensitiveValue` entries wrapped,
so storage never receives their values. The gateway unwraps them only for the request to the provider,
with `toUnsafeArray()`. See [Handlers](gateways/handlers.md) if you are writing a gateway.

## Mask a card number

To show a card number to the customer or to support staff, mask it with `Payum\Core\Security\Util\Mask`.
It keeps the first character and the last four, replaces the rest with `X`, and leaves separators such
as `-`, `_` and spaces in place:

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

## The credit card model

`Payum\Core\Model\CreditCard` holds card details. Its setters wrap the number, holder, security code
and expiry date in `SensitiveValue`, and it keeps a masked copy of the number and the holder for
display:

```php
<?php

use Payum\Core\Model\CreditCard;

$card = new CreditCard();
$card->setNumber('4111111111111111');
$card->setHolder('Jane Doe');
$card->setSecurityCode('123');
$card->setExpireAt(new DateTime('2030-12-31'));

echo $card->getMaskedNumber(); // 4XXXXXXXXXXX1111
echo $card->getMaskedHolder(); // JXXX XXX
```

{% hint style="warning" %}
Do not store or serialize a `CreditCard`. The object still holds the raw number, holder and security code
in plain properties, so `serialize()` writes them out. Store only what you need for display, such as the
masked number and the brand.
{% endhint %}
