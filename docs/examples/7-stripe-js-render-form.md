<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

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
