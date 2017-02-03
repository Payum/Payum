# Payout script.

This is the script which does all the job related to payout payments.

## Secured script.

```php
<?php
//payout.php

use Payum\Core\Request\Payout;
use Payum\Core\Payum;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;

include __DIR__.'/config.php';

/** @var Payum $payum */

$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

try {
    $gateway->execute(new Payout($token));

    if (false == isset($_REQUEST['noinvalidate'])) {
        $payum->getHttpRequestVerifier()->invalidate($token);
    }

    header("Location: ".$token->getAfterUrl());
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

_**Note**: If you've got the "Unsupported reply" you have to add an if condition for that reply. Inside the If statement you have to convert the reply to http response._

This is how you can create a payout url.

```php
<?php

use Payum\Core\Payum;

include __DIR__.'/config.php';

/** @var Payum $payum */

$token = $payum->getTokenFactory()->createPayoutToken($gatewayName, $details, 'afterPayoutUrl');

header("Location: ".$token->getTargetUrl());
```

Back to [examples](index.md).
Back to [index](../index.md).
