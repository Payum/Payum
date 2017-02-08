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
