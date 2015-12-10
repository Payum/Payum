# Notify script.

You can use this script if a gateway allows setting notification url per payment, like Paypal.

```php
<?php
//notify.php

use Payum\Core\Request\Notify;
use Payum\Core\Reply\HttpRedirect;

include 'config.php';

$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

try {
    $gateway->execute(new Notify($token));

    http_response_code(204);
    echo 'OK';
} catch (HttpResponse $reply) {
    foreach ($reply->getHeaders() as $name => $value) {
        header("$name: $value");
    }

    http_response_code($reply->getStatusCode());
    echo ($reply->getContent());

    exit;
} catch (ReplyInterface $reply) {
    throw new \LogicException('Unsupported reply', null, $reply);
}
```

You have to use this script if a gateway does not allows setting notification url per payment, like Be2Bill.

Back to [scripts](https://github.com/Payum/Core/tree/master/Resources/docs/scripts/index.md).
Back to [index](https://github.com/Payum/Core/tree/master/Resources/docs/index.md).


