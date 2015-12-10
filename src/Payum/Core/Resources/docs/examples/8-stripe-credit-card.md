# 3. Stripe.Js. Render form. 

```php
<?php

$card = new \Payum\Core\Model\CreditCard();
$card->setNumber('4111111111111111');
$card->setExpireAt(new \DateTime('2018-10-10'));
$card->setSecurityCode('123');

$model = new \Payum\Model\Payment();
$model->setCurrencyCode('USD');
$model->setTotalAmount(1);
$model->setCreditCard($card);

$gateway->execute(new \Payum\Core\Request\Capture($model));
```

Back to [examples](index.md).
Back to [index](https://github.com/Payum/Core/tree/master/Resources/docs/index.md).
