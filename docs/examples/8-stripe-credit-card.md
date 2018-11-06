<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# 3. Stripe.Js. Render form. 

```php
<?php

use Payum\Core\Model\Payment;
use Payum\Core\Model\CreditCard;
use Payum\Core\GatewayInterface;

$card = new CreditCard();
$card->setNumber('4111111111111111');
$card->setExpireAt(new \DateTime('2018-10-10'));
$card->setSecurityCode('123');

$model = new Payment();
$model->setCurrencyCode('USD');
$model->setTotalAmount(1);
$model->setCreditCard($card);

/** @var GatewayInterface $gateway */
$gateway->execute(new \Payum\Core\Request\Capture($model));
```

Back to [examples](index.md).
Back to [index](../index.md).
