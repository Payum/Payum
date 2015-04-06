# 3. Stripe.Js. Render form. 

```php
<?php

try {
    $gateway->execute(new \Payum\Core\Request\Capture($model);
} catch (Reply) {
    if ($reply instanceof Payum\Core\Reply\HttpResponse) {
        echo $reply->getContent();
        
        exit;
    }
    
    throws \LogicException('Unsupported reply', null, $reply);
}
```