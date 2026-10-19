# BC conformance suite

Proves that a 1.x gateway still works on 2.x. Each file in `Scenarios/` scripts operations against one or more gateways. `golden/` holds what Payum 1.x did for every one of them: the reply thrown, the status, the details persisted, the HTTP calls made. Each `*BcTest` replays its scenarios on 2.x twice, once with 1.x requests and once with 2.x commands through `LegacyGatewayAdapter`, and compares the result with the golden file.

**The golden files only ever come from a 1.x run.** Never edit one by hand, and never re-record one because a test fails on 2.x: a failure here is a break with 1.x. Fix it, or, when the difference is wanted, list it in that test's `accepted()` and in the exception list for `UPGRADE.md`.

To record, after adding or changing a scenario:

```bash
mkdir /tmp/payum-1x && cd /tmp/payum-1x
composer require payum/payum:1.7.7 php-http/guzzle7-adapter php-http/message-factory nyholm/psr7 twig/twig:^3 \
    stripe/stripe-php:7.128.0 authorizenet/authorizenet:1.9.9 klarna/checkout:2.0.0 \
    paypal/rest-api-sdk-php:1.6.4 sofort/sofortlib-php:3.3.2 solidworx/klarna-invoice:0.2.1

cd -
php src/Payum/Core/Tests/Bc/record.php /tmp/payum-1x/vendor/autoload.php [src/Payum/Core/Tests/Bc/Scenarios/<file>.php ...]
```

`record.php` refuses to run against a 2.x autoloader. Everything it loads from this directory (`Recorder`, the scenarios, `Fixture/` apart from the mixed gateway) uses only API that 1.x and 2.x share.

Gateways whose SDK talks to its PSP directly (Authorize.Net, Klarna, Payex, PayPal REST, Sofort) are covered by action selection and status scenarios only. `payum.http_client` and the Stripe SDK are mocked, so those gateways also run their flows.
