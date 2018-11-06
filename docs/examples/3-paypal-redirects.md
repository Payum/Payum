<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# 3. Paypal. Handle redirect. 

```php
<?php

use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Model\Payment;
use Payum\Core\Reply\ReplyInterface;

try {
    /** @var array|\ArrayObject|Payment $model */
    
    /** @var GatewayInterface $gateway */
    $gateway->execute(new Capture($model));
} catch (ReplyInterface $reply) {
    if ($reply instanceof HttpRedirect) {
        header("Location: ".$reply->getUrl());
        
        exit;
    }
    
    throw new \LogicException('Unsupported reply', null, $reply);
}
```

Back to [examples](index.md).
Back to [index](../index.md).
