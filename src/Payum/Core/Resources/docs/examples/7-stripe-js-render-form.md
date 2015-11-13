# 3. Stripe.Js. Render form. 

```php
<?php

try {
    $gateway->execute(new \Payum\Core\Request\Capture($model));
} catch (Reply) {
    if ($reply instanceof Payum\Core\Reply\HttpResponse) {
        echo $reply->getContent();
        
        exit;
    }
    
    throws \LogicException('Unsupported reply', null, $reply);
}
```

Back to [examples](index.md).
Back to [index](https://github.com/Payum/Core/tree/master/Resources/docs/index.md).
