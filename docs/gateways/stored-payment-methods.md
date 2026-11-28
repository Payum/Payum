# Stored payment methods

An application saves a customer's card or bank account at the PSP and charges it again later, as
[Save a payment method and charge it later](../stored-payment-methods.md) shows. Payum keeps the record of
each stored method and decides who started each charge. Your gateway tells the PSP to save the method,
charges the PSP's reference for it, and returns what the PSP stored. This page shows the handlers that do
it.

The Offline gateway in `src/Payum/Offline` implements everything on this page without a PSP. Read its
handlers next to this page.

## Declare the capabilities

Two capabilities come from handlers, and two you declare:

| Capability | Where it comes from | Means |
| :--- | :--- | :--- |
| `SavePaymentMethod` | A `SavePaymentMethodHandlerInterface` handler | The gateway saves a method without taking a payment. |
| `DeletePaymentMethod` | A `DeletePaymentMethodHandlerInterface` handler | The gateway removes a method at the PSP. |
| `StoredPaymentMethods` | Declared | The capture and authorize handlers save the method during the payment. |
| `Recurring` | Declared | The capture and authorize handlers charge a saved method with no customer present. |

```php
<?php

declare(strict_types=1);

namespace Acme\Payum;

use Acme\Payum\Handler\CaptureHandler;
use Acme\Payum\Handler\DeletePaymentMethodHandler;
use Acme\Payum\Handler\SavePaymentMethodHandler;
use Payum\Core\Gateway\Capability;
use Payum\Core\Gateway\DeclaresCapabilities;
use Payum\Core\Gateway\GatewayInterface;

final class AcmeGateway implements GatewayInterface, DeclaresCapabilities
{
    public function handlers(): array
    {
        return [
            CaptureHandler::class,
            SavePaymentMethodHandler::class,
            DeletePaymentMethodHandler::class,
        ];
    }

    public function capabilities(): array
    {
        return [
            Capability::StoredPaymentMethods,
            Capability::Recurring,
        ];
    }

    // name(), logo(), websiteUrl() and configClass() as in Defining a gateway
}
```

Payum enforces the two declared capabilities. Before your handler runs, it refuses a payment that would
save a method on a gateway without `StoredPaymentMethods`, and a merchant-initiated charge on a gateway
without `Recurring`. Both throw `Payum\Core\Exception\UnsupportedOperationException`. A gateway that
implements no `DeclaresCapabilities` at all, as every 1.x gateway, is let through.

## Save and charge in the capture handler

`$context->storedCredential()` tells the handler what to do. It is null when the payment has no stored
method, and a `Payum\Core\Handler\StoredCredential` otherwise:

| Property | Holds |
| :--- | :--- |
| `isFirst` | `true` when the method has no reference yet: ask the PSP to save it during this payment. |
| `initiator` | `Initiator::Customer` when the command arrived on a token or the caller said so, `Initiator::Merchant` otherwise. |
| `reason` | `ChargeReason::Unscheduled`, `Recurring` or `Installment`. |
| `previousNetworkTransactionId` | The network's id for the first charge, for PSPs that want it on later ones. |

This capture handler sends the stored-method fields Acme takes, and returns what Acme saved:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum\Handler;

use Acme\Payum\AcmeApi;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Command\Initiator;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Model\PaymentMethodType;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\StoredPaymentMethodDetails;

final class CaptureHandler implements CaptureHandlerInterface
{
    public function __construct(
        private readonly AcmeApi $api,
    ) {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        $amount = $context->amount();
        $credential = $context->storedCredential();

        $request = [
            'amount' => (int) $amount->getAmount(),
            'currency' => $amount->getCurrency()->getCode(),
            'metadata' => ['payum_reference' => $context->merchantReference()],
        ];

        if (null !== $credential) {
            $request['customer'] = $context->customer()?->getGatewayCustomerId((string) $context->gatewayName());
            $request['off_session'] = Initiator::Merchant === $credential->initiator;

            if ($credential->isFirst) {
                $request['save_payment_method'] = true;
            } else {
                $request['payment_method'] = $context->storedPaymentMethod()?->getReference();
            }
        }

        $charge = $this->api->charge($request);
        $result = CaptureResult::captured($charge['id']);

        if (true !== $credential?->isFirst) {
            return $result;
        }

        return $result->withStoredPaymentMethod(StoredPaymentMethodDetails::active(
            $charge['payment_method']['id'],
            type: PaymentMethodType::Card,
            brand: $charge['payment_method']['brand'],
            last4: $charge['payment_method']['last4'],
            expiryMonth: $charge['payment_method']['exp_month'],
            expiryYear: $charge['payment_method']['exp_year'],
            networkTransactionId: $charge['network_transaction_id'],
            gatewayCustomerId: $charge['customer'],
        ));
    }
}
```

`AcmeApi::charge()` stands for your PSP client. Do not look the stored method or customer up yourself:
`$context->storedPaymentMethod()` and `$context->customer()` return the ones linked to the payment. Pass
the customer's id for this gateway name, because one customer can have an id at several PSPs.

`withStoredPaymentMethod()` hands Payum a `Payum\Core\Result\StoredPaymentMethodDetails`. Payum writes it
onto the stored method, records `gatewayCustomerId` on the customer, saves both, and remembers the
reference so later webhooks about the method find it. A null field leaves the method's value as it is.
`active()` sets the status to `Active`. Build the object with `new` and a `status:` to report another
status, such as `StoredPaymentMethodStatus::Pending` for a method the PSP confirms later.

An authorize handler does the same with `AuthorizeResult`.

## Tell the PSP who started the charge

Card networks require every charge on a stored card to say whether the customer was present and why the
merchant charges. Each PSP names the fields differently. Map the `StoredCredential` to them:

| PSP | Saving (`isFirst`) | Customer present (`Initiator::Customer`) | Merchant-initiated (`Initiator::Merchant`) |
| :--- | :--- | :--- | :--- |
| Stripe | `setup_future_usage: off_session` | `payment_method`, `customer` | `off_session: true`, `confirm: true` |
| Adyen | `storePaymentMethod: true`, `shopperInteraction: Ecommerce` | `shopperInteraction: Ecommerce`, `recurringProcessingModel: CardOnFile` | `shopperInteraction: ContAuth`, `recurringProcessingModel: Subscription` or `UnscheduledCardOnFile` |
| Mollie | `sequenceType: first` | `sequenceType: oneoff` | `sequenceType: recurring`, `mandateId` |
| Checkout.com | `merchant_initiated: false`, `payment_type: Regular` | `merchant_initiated: false` | `merchant_initiated: true`, `payment_type: Recurring` or `Unscheduled`, `previous_payment_id` |
| PayPal | `stored_credential.usage: FIRST` | `stored_credential.payment_initiator: CUSTOMER`, `usage: SUBSEQUENT` | `payment_initiator: MERCHANT`, `payment_type: RECURRING` or `UNSCHEDULED`, `previous_network_transaction_reference` |
| Braintree | `options.storeInVaultOnSuccess: true` | none | `transactionSource: recurring` or `unscheduled` |

Pick between the "or" values with `$credential->reason`: `ChargeReason::Recurring` for a subscription,
`Unscheduled` for a charge at an irregular time, `Installment` for a purchase split into parts. Check the
PSP's API reference for the exact field paths. For the PSPs that want the first charge's network id on
each later one (Checkout.com, PayPal cards), return it once as `networkTransactionId` and read it back
from `$credential->previousNetworkTransactionId`.

## Save a method without a payment

A `SavePaymentMethodHandlerInterface` handler saves a method with no money moving: a Stripe SetupIntent,
a zero-amount authorization, a Mollie first payment or a GoCardless mandate. It runs like a capture: when
the customer has to confirm at the PSP, return `pending()` with a redirect, and the same command runs
again when the PSP sends the customer back:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum\Handler;

use Acme\Payum\AcmeApi;
use Payum\Core\Command\SavePaymentMethodCommand;
use Payum\Core\Handler\Context;
use Payum\Core\Handler\SavePaymentMethodHandlerInterface;
use Payum\Core\Model\PaymentMethodType;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\SavePaymentMethodResult;
use Payum\Core\Result\StoredPaymentMethodDetails;

final class SavePaymentMethodHandler implements SavePaymentMethodHandlerInterface
{
    public function __construct(
        private readonly AcmeApi $api,
    ) {
    }

    public function handle(SavePaymentMethodCommand $command, Context $context): SavePaymentMethodResult
    {
        $state = $context->state();

        if (! isset($state['setup_id'])) {
            $setup = $this->api->createSetup([
                'customer' => $context->customer()?->getGatewayCustomerId((string) $context->gatewayName()),
                'return_url' => $context->token()?->getTargetUrl(),
            ]);
            $state['setup_id'] = $setup['id'];

            return SavePaymentMethodResult::pending(new Redirect($setup['url']));
        }

        $setup = $this->api->findSetup($state['setup_id']);

        return SavePaymentMethodResult::saved(StoredPaymentMethodDetails::active(
            $setup['payment_method']['id'],
            type: PaymentMethodType::Card,
            last4: $setup['payment_method']['last4'],
            gatewayCustomerId: $setup['customer'],
        ), $setup['id']);
    }
}
```

`$context->state()` is the stored method's details, so the setup id survives the redirect. The result
has four forms:

| Result | Use it when |
| :--- | :--- |
| `SavePaymentMethodResult::saved($details, $transactionId)` | The PSP saved the method. The transaction id is optional: pass the PSP's id for the setup when a later webhook names it. |
| `SavePaymentMethodResult::pending($next)` | The customer has to do something first, such as confirm with their bank. |
| `SavePaymentMethodResult::accepted($transactionId)` | The PSP confirms later by webhook, as for a direct debit mandate. |
| `SavePaymentMethodResult::failed($failure)` | The PSP refused. The method is left as it was. |

## Delete a method

A `DeletePaymentMethodHandlerInterface` handler removes the method at the PSP. `deleted()` marks it
`Revoked`:

```php
public function handle(DeletePaymentMethodCommand $command, Context $context): DeletePaymentMethodResult
{
    $this->api->detach((string) $command->method()->getReference());

    return DeletePaymentMethodResult::deleted();
}
```

Return `DeletePaymentMethodResult::failed($failure)` when the PSP refuses, and Payum leaves the method
`Active`.

## Webhooks about a method

PSPs send webhooks when a method changes: a card updater replaced an expired card, the customer's bank
cancelled a mandate, or the PSP disabled a token. Return the method's reference from `verify()`, as
[Webhooks](webhooks.md#naming-what-the-message-is-about) shows for payments:

```php
return WebhookEvent::verified($event, $event['id'], $event['type'], reference: $event['data']['payment_method']);
```

Payum finds the stored method with that reference and passes it to `handle()` as
`$context->storedPaymentMethod()`. Return the new details on the result:

```php
public function handle(NotifyCommand $command, WebhookEvent $event, Context $context): NotifyResult
{
    if ('payment_method.updated' !== $event->type || null === $context->storedPaymentMethod()) {
        return NotifyResult::ignored();
    }

    return NotifyResult::handled()->withStoredPaymentMethod(new StoredPaymentMethodDetails(
        last4: $event->payload['data']['last4'],
        expiryMonth: $event->payload['data']['exp_month'],
        expiryYear: $event->payload['data']['exp_year'],
    ));
}
```

For a cancelled mandate, return `StoredPaymentMethodDetails::revoked()`. For an expired card, return
`new StoredPaymentMethodDetails(status: StoredPaymentMethodStatus::Expired)`. Payum records the change and
dispatches `PaymentMethodStatusChanged` when the status moved.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
