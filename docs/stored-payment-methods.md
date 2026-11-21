# Save a payment method and charge it later

A stored payment method is a card, bank account or wallet that the payment provider (PSP) keeps for
you, so you can charge it again without the customer typing it in. Payum keeps a record of each one:
which customer it belongs to, the PSP's reference for it, what to show the customer ("Visa ending
4242") and whether it can still be charged. The card number itself never reaches your application.

This page uses the Offline gateway, which stores methods without talking to any PSP, so you can try
every step before you have an account. The scripts work the same with any gateway that supports
stored methods.

## Check that the gateway supports it

Ask the gateway for its capabilities before you offer a "save my card" box:

```php
<?php

use Payum\Core\Gateway\Capability;

$capabilities = $payum->getGateway('offline')->capabilities();

$canSave = in_array(Capability::StoredPaymentMethods, $capabilities, true);
$canChargeLater = in_array(Capability::Recurring, $capabilities, true);
```

Each capability unlocks one part of this page:

| Capability | What the gateway can do |
| :--- | :--- |
| `StoredPaymentMethods` | Save the method while the customer pays. |
| `Recurring` | Charge a saved method while the customer is not there, for example from a cron job. |
| `SavePaymentMethod` | Save a method without taking a payment. |
| `DeletePaymentMethod` | Remove a saved method at the PSP. |

Payum checks the first two for you. A charge that needs one the gateway does not declare throws
`Payum\Core\Exception\UnsupportedOperationException` before anything is sent to the PSP.

## Set up

Register a storage for customers and one for stored methods next to your payments. `addDefaultStorages()`
registers file storages for `Payum\Core\Model\Customer` and `Payum\Core\Model\StoredPaymentMethod`, which
is enough to try this page:

```php
<?php
// config.php

use Payum\Core\PayumBuilder;
use Payum\Offline\OfflineConfig;

require __DIR__.'/vendor/autoload.php';

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('offline', new OfflineConfig())
    ->getPayum();
```

For real payments, store all three in your database. The payment's links to its customer and its stored
method are yours to map, because they point at your own classes. [Storages](storages.md#store-customers-and-payment-methods)
shows the Doctrine entities.

## The customer

A stored method belongs to a customer. Payum ships `Payum\Core\Model\Customer`, which you create once per
user and save:

```php
<?php

use Payum\Core\Model\Customer;

$customer = new Customer();
$customer->setCustomerId('customer-42');
$customer->setEmail('customer@example.com');

$payum->getStorage(Customer::class)->update($customer);
```

`customerId` is your own id for the user. Most PSPs also create a customer of their own, and Payum
records the PSP's id on the customer for you, one per gateway name, so the same user can pay through
two PSPs.

To use your own `User` entity as the customer instead, implement `Payum\Core\Model\CustomerInterface`
and use `Payum\Core\Model\CustomerTrait`, which keeps the PSP ids in one array:

```php
<?php

namespace Acme\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\CustomerInterface;
use Payum\Core\Model\CustomerTrait;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements CustomerInterface
{
    use CustomerTrait;

    /**
     * @var array<string, string>
     */
    #[ORM\Column(type: 'json')]
    protected array $gatewayCustomerIds = [];

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string')]
    private string $email = '';

    public function getCustomerId(): string
    {
        return (string) $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return null;
    }

    public function getDetails(): array
    {
        return [];
    }

    public function setDetails($details): void
    {
    }
}
```

The trait declares `$gatewayCustomerIds` but cannot map it, so redeclare it with a `json` column as the
sample does. Register a storage for `User::class`.

## Save the method during the first payment

Create a stored method with no reference, link it to the payment, and take the payment as usual. This
`prepare.php` replaces the one from [Get started](get-it-started.md):

```php
<?php
// prepare.php

use Payum\Core\Model\Customer;
use Payum\Core\Model\Payment;
use Payum\Core\Model\StoredPaymentMethod;

require __DIR__.'/config.php';

$customer = $payum->getStorage(Customer::class)->find('customer-42');

$method = new StoredPaymentMethod();
$method->setGatewayName('offline');
$method->setCustomer($customer);
$payum->getStorage(StoredPaymentMethod::class)->update($method);

$payment = new Payment();
$payment->setNumber(uniqid());
$payment->setCurrencyCode('EUR');
$payment->setTotalAmount(1000);
$payment->setCustomer($customer);
$payment->setStoredPaymentMethod($method);

$captureToken = $payum->prepare('offline', $payment);

header('Location: '.$captureToken->getTargetUrl());
```

`find('customer-42')` works because the file storage identifies a `Customer` by its `customerId`. With a
database, look the customer up by your own id, the way you load any other entity.

A method without a reference tells the gateway to save it during this payment. `capture.php` stays as it
is. When the PSP confirms the payment, Payum writes the PSP's reference, the display details and the
`Active` status onto the method and saves it.

Read the method in `done.php`:

```php
<?php
// done.php

use Payum\Core\Result\StoredPaymentMethodStatus;

require __DIR__.'/config.php';

$payment = $payum->done();
$method = $payment->getStoredPaymentMethod();

if (StoredPaymentMethodStatus::Active === $method?->getStatus()) {
    echo 'Your payment method is saved for next time.';
}
```

The status is a `Payum\Core\Result\StoredPaymentMethodStatus`:

| Status | Means |
| :--- | :--- |
| `Pending` | Not saved yet, or the PSP has not confirmed it. |
| `Active` | You can charge it. |
| `Revoked` | You or the customer removed it, or the bank cancelled the mandate. |
| `Expired` | The card expired. |
| `Failed` | The PSP refused to save it. |

A status Payum does not recognise, such as one written into the database by hand, reads as `Pending`.

## Charge a saved method

To charge the customer while they are not there, such as a monthly fee from a cron job, create a payment
that carries the saved method, save it, and capture it:

```php
<?php

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Model\Payment;
use Payum\Core\Model\StoredPaymentMethod;
use Payum\Core\Result\StoredPaymentMethodStatus;

$method = $payum->getStorage(StoredPaymentMethod::class)->find($methodId);

if (StoredPaymentMethodStatus::Active !== $method->getStatus()) {
    // ask the customer for a new payment method
    return;
}

$payment = new Payment();
$payment->setNumber(uniqid());
$payment->setCurrencyCode('EUR');
$payment->setTotalAmount(1000);
$payment->setCustomer($method->getCustomer());
$payment->setStoredPaymentMethod($method);
$payum->getStorage(Payment::class)->update($payment);

$result = $payum->getGateway($method->getGatewayName())->execute(CaptureCommand::forPayment($payment));

$payum->getStorage(Payment::class)->update($payment);
```

`$methodId` is the id you kept for the method, for example on the user's subscription. Use the gateway
the method was saved with: a reference from one PSP means nothing to another.

{% hint style="warning" %}
Save the payment before you execute the command. Payum throws a `LogicException` for a charge on a stored
method whose payment has no id, because it could not match the PSP's later messages to it.
{% endhint %}

A command built with `forPayment()` has no token, so the gateway tells the PSP that you started the
charge (a merchant-initiated charge). That needs `Capability::Recurring`. A command that arrives on a token,
such as the capture in `capture.php`, counts as started by the customer. When the customer is present
but the charge comes from your own code, for example a one-click checkout over your API, say so:

```php
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\Initiator;

$result = $gateway->execute(CaptureCommand::forPayment($payment, initiator: Initiator::Customer));
```

Card networks also want to know why you charge. Payum classifies the charge as `Unscheduled` unless you
pass a `Payum\Core\Command\ChargeReason`, such as `reason: ChargeReason::Installment` for a purchase split
into parts. `AuthorizeCommand` takes the same two arguments.

The result is the same as for any capture. Check `$result->isFailed()` and read `$result->failure`, as
[Payment operations](payment-operations.md#declines-and-errors) describes. A declined charge leaves the
method `Active`, so you can try again later.

## Save a method without taking a payment

Some applications save a card before the first charge, such as at the start of a free trial. Create the
method and a `SavePaymentMethod` token, then send the customer to it:

```php
<?php
// save.php

use Payum\Core\Model\Customer;
use Payum\Core\Model\StoredPaymentMethod;
use Payum\Core\Security\TokenPurpose;

require __DIR__.'/config.php';

$method = new StoredPaymentMethod();
$method->setGatewayName('offline');
$method->setCustomer($payum->getStorage(Customer::class)->find('customer-42'));
$payum->getStorage(StoredPaymentMethod::class)->update($method);

$token = $payum->getTokenIssuer()->create(TokenPurpose::SavePaymentMethod, 'offline', $method, 'saved.php');

header('Location: '.$token->getTargetUrl());
```

The token's target is `save-payment-method.php`, which runs the gateway the same way `capture.php` does:

```php
<?php
// save-payment-method.php

require __DIR__.'/config.php';

$payum->savePaymentMethod()->send();
```

The PSP may send the customer to their bank to confirm, and back to this script. Once the method is
saved, the customer lands on `saved.php`, which loads the method from the token:

```php
<?php
// saved.php

use Payum\Core\Result\StoredPaymentMethodStatus;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/config.php';

$token = $payum->getHttpRequestVerifier()->verify(Request::createFromGlobals());
$payum->getHttpRequestVerifier()->invalidate($token);

$identity = $token->getDetails();
$method = $payum->getStorage($identity->getClass())->find($identity);

if (StoredPaymentMethodStatus::Active === $method->getStatus()) {
    echo 'Your payment method is saved.';
}
```

Some methods, such as a direct debit mandate, are confirmed by the PSP hours later. The method stays
`Pending` until the PSP's webhook arrives and Payum marks it `Active`. To receive those webhooks, see
[Payment notifications](instant-payment-notification.md).

To save a method from your own code, with no customer redirect, execute the command yourself:

```php
use Payum\Core\Command\SavePaymentMethodCommand;
use Payum\Core\Model\StoredPaymentMethod;

$result = $payum->getGateway('offline')->execute(SavePaymentMethodCommand::forMethod($method));

$payum->getStorage(StoredPaymentMethod::class)->update($method);
```

Payum saves the method when the PSP reports what it stored, but anything else the gateway writes onto a
method you handed it is yours to save, so save it after the command returns. The result has no payment
status. Read the method's status instead, and act on `$result->next` when the customer has to do
something, as for a capture.

## Delete a saved method

When the customer removes a card from their account, delete it at the PSP:

```php
<?php

use Payum\Core\Command\DeletePaymentMethodCommand;

$result = $payum->getGateway($method->getGatewayName())->execute(DeletePaymentMethodCommand::forMethod($method));

if ($result->isFailed()) {
    // the PSP still has it; try again later
}
```

Payum marks the method `Revoked` and saves it. Keep the record: the payments made with it still point at
it.

## Know when a method changes

Payum dispatches `Payum\Core\Event\PaymentMethodStatusChanged` each time a method moves from one status to
another: when it is saved, when you delete it, and when the PSP reports that it expired or the customer's
bank cancelled it. Ask the customer for a new method when that happens:

```php
<?php

use Payum\Core\Event\PaymentMethodStatusChanged;
use Payum\Core\Result\StoredPaymentMethodStatus;

$dispatcher->addListener(PaymentMethodStatusChanged::class, function (PaymentMethodStatusChanged $event) use ($mailer): void {
    if (StoredPaymentMethodStatus::Expired === $event->to || StoredPaymentMethodStatus::Revoked === $event->to) {
        $mailer->askForNewPaymentMethod($event->method->getCustomer());
    }
});
```

`$event->method` is the stored method, and `$event->from` and `$event->to` are the old and new status. A
PSP that sends the same webhook twice does not fire the event twice. [Events](events.md) shows how to
register a dispatcher.

## What Payum does not do

Payum does not fetch a customer's saved methods from the PSP. Your storage is the list: query it for the
customer's `Active` methods to show them. Payum does not renew expiring cards either. When a PSP updates a
card itself and sends a webhook about it, Payum records the new details and status.

## Charge on a schedule

To charge a saved method every month, link the payments to a subscription. Payum then tells your
listeners about each charge and whether it was the first. Your cron job can do the charging, or a PSP that
runs subscriptions can. See [Charge a customer on a schedule](subscriptions.md).

## Advanced: write a gateway that stores methods

A gateway supports stored methods by declaring the capabilities and returning what the PSP stored from
its handlers. [Stored payment methods in a gateway](gateways/stored-payment-methods.md) covers the
handlers, the details to return and how to tell each PSP who started a charge.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
