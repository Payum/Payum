# 3. Paypal. Handle redirect. 

```php
<?php


try {
    $gateway->execute(new \Payum\Core\Request\Capture($model);
} catch (Payum\Core\Reply\ReplyInterface $reply) {
    if ($reply instanceof Payum\Core\Reply\HttpRedirect) {
        header("Location: ".$reply->getUrl());
        
        exit;
    }
    
    throw new \LogicException('Unsupported reply', null, $reply);
}
```

Back to [examples](index.md).
Back to [index](https://github.com/Payum/Core/tree/master/Resources/docs/index.md).