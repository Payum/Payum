<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://github.com/sponsors/payum)

---

# Offline
[![Total Downloads](https://poser.pugx.org/payum/offline/d/total.png)](https://packagist.org/packages/payum/offline) [![Latest Stable Version](https://poser.pugx.org/payum/offline/version.png)](https://packagist.org/packages/payum/offline)

Payments that are settled outside an online payment provider: cash, cheque, bank transfer, store credit.
No external API is called. The gateway records each operation in the payment's details and reports its
status.

## Register it

Register the gateway once for each payment method you offer:

```php
<?php

use Payum\Core\PayumBuilder;
use Payum\Offline\OfflineConfig;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('cash', new OfflineConfig())
    ->registerGateway('bank_transfer', new OfflineConfig(paid: false))
    ->getPayum();
```

A captured payment is paid at once unless the config says `paid: false`. Then it stays pending until you
set its `paid` detail to `true` and capture it again. A `paid` detail set on the payment before the capture
always wins.

It answers capture, authorize, refund (also partial), cancel, sync and payout.

## Replace a handler

Give your own handler for an operation that has to do more, such as taking the amount off a customer's
store credit. It replaces the gateway's handler for that command on this registration only:

```php
<?php

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;

final class StoreCreditCaptureHandler implements CaptureHandlerInterface
{
    public function __construct(private readonly StoreCreditLedger $ledger)
    {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        $payment = $context->payment();

        if (! $this->ledger->withdraw($payment->getClientId(), $context->amount())) {
            return CaptureResult::failed(new Failure(FailureReason::InsufficientFunds));
        }

        return CaptureResult::captured();
    }
}

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGlobalService(StoreCreditLedger::class, $ledger)
    ->registerGateway('store_credit', new OfflineConfig(handlers: [StoreCreditCaptureHandler::class]))
    ->getPayum();
```

A class name is autowired, with services registered by `addGlobalService()`. You can pass an instance
instead. The status your handler returns is written to the payment's `status` detail, so the other
operations see it.

## Extend it

`OfflineConfig` and `OfflineGateway` can be extended. A `StoreCreditConfig extends OfflineConfig` passes its
handlers to the parent constructor and can add settings of its own. A `StoreCreditGateway extends
OfflineGateway` gives the method its own name, logo and handler list. Point the config's
`getGatewayClass()` at it and the gateway's `configClass()` at the config. See the
[Offline docs](https://payum.dev/docs/offline) for an example.


## Resources

* [Site](https://payum.dev/)
* [Documentation](https://payum.dev/docs/offline)
* [Questions](http://stackoverflow.com/questions/tagged/payum)
* [Issue Tracker](https://github.com/Payum/Payum/issues)
* [Twitter](https://twitter.com/payumphp)

## License

Offline is released under the [MIT License](LICENSE).
