# Refund script.

```php
<?php
//refund.php

use Payum\Core\Request\Refund;
use Payum\Core\Reply\HttpRedirect;

include 'config.php';

$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

try {
    $gateway->execute(new Notify($token));

    if (false == isset($_REQUEST['noinvalidate'])) {
        $payum->getRequestVerifier()->invalidate($token);
    }

    if ($token->getAfterUrl()) {
        header("Location: ".$token->getAfterUrl());
    } else {
        http_response_code(204);
        echo 'OK';
    }
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

Back to [scripts](https://github.com/Payum/Core/tree/master/Resources/docs/scripts/index.md).
Back to [index](https://github.com/Payum/Core/tree/master/Resources/docs/index.md).

