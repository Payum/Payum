# Authorise script.

This is the script which does all the job related to payments authorization. 
It may show a credit card form, an iframe or redirect a user to gateway side. 
The action provides some basic security features. It is completely unique for each payment, and once we done the url invalidated.
When the authorization is done a user is redirected to after url, in our case it is [done script](done-script.md).

```php
<?php
//authorise.php

use Payum\Core\Payum;
use Payum\Core\Request\Authorize;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;

include __DIR__.'/config.php';

/** @var Payum $payum */

$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

try {
    $gateway->execute(new Authorize($token));

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

This is how you can create a authorize url.

Back to [examples](index.md).
Back to [index](../index.md).
