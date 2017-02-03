# 3. Stripe.Js. Render form. 

```php
<?php

use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Model\Payment;

/** @var array|\ArrayObject|Payment $model */

try {
    /** @var GatewayInterface $gateway */
    $gateway->execute(new Capture($model));
} catch (ReplyInterface $reply) {
    if ($reply instanceof HttpResponse) {
        echo $reply->getContent();
        
        exit;
    }
    
    throw new \LogicException('Unsupported reply', null, $reply);
}
```

Back to [examples](index.md).
Back to [index](../index.md).
