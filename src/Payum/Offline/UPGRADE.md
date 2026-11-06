# Upgrades

## 2.0.0

`OfflineGatewayFactory` is deprecated and removed in 3.0. Register an `OfflineConfig` instead:

```php
// 1.x
$builder->addGateway('offline', ['factory' => 'offline']);

// 2.0
$builder->registerGateway('offline', new \Payum\Offline\OfflineConfig());
```

- The factory keeps working as before, with its actions, `payum.action.*` keys and options.
- The actions under `Action/` are deprecated with the factory. `OfflineGateway` runs handlers instead and
  ignores `payum.action.*` entries, including a custom `payum.action.convert_payment`. Put what it added
  onto the payment's details before you execute the command, or replace a handler through
  `OfflineConfig(handlers: [...])`.
- Payment details keep their keys (`paid`, `payout`, `status`, `amount`, `currency`, …), so payments
  created with 1.x carry on. A partial refund adds `refunded_amount`.
- The `status` detail can now also hold `partially_refunded`. The status action reads every
  `Payum\Core\Result\PaymentStatus` value, so a `status` of `failed`, `expired` or `suspended` is now
  reported as such instead of unknown.

See https://github.com/Payum/Payum/blob/2.x/UPGRADE.md for Payum itself.
