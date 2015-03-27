# 3. Paypal. Handle redirect. 

```php
<?php


try {
    $payment->execute(new \Payum\Core\Request\Capture($model);
} catch (Payum\Core\Reply\ReplyInterface $reply) {
    if ($reply instanceof Payum\Core\Reply\HttpRedirect) {
        header("Location: ".$reply->getUrl());
        
        exit;
    }
    
    throws \LogicException('Unsupported reply', null, $reply);
}
```