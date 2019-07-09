<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# Paypal Express Checkout. Confirm order step.

Paypal official documentation [suggest you to show a confirm order page](https://developer.paypal.com/docs/classic/express-checkout/integration-guide/ECGettingStarted/#id084RN0F0OPN), once user is back from Paypal.
By default Payum skip this step, at Paypal site you will see "Pay Now" button. If you want to use confirm step you have to reset
`AUTHORIZE_TOKEN_USERACTION`. Set it to empty string

```php
<?php
// prepare.php

$payment->setDetails(array(
  'AUTHORIZE_TOKEN_USERACTION' => '',
));
```

That's it. Payum will render a page with a confirm button. The page is pretty simple and you most likely want to customize it.
You can tell the gateway to use your own template by providing it in the gateway config.

```php
<?php
// config.php

use Payum\Core\PayumBuilder;
use Payum\Core\Payum;

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addGateway('gatewayName', [
        'factory' => 'paypal_express_checkout',
        'payum.template.confirm_order' => '@Acme/confirm_paypal_order.html.twig',
    ])

    ->getPayum()
;
```

Back to [index](../../index.md).
