# Capture script.

This is the script which does all the job related to capturing payments. 
It may show a credit card form, an iframe or redirect a user to gateway side.
Each capture url is completely unique for each purchase, and once we done the url is invalidated and no more accessible.
When the capture is done a user is redirected to after url, in our case it is [done script](https://github.com/Payum/Core/tree/master/Resources/docs/scripts/done-script.md).

## Secured script.

```php
<?php
//capture.php

use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpRedirect;

include 'config.php';

$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

try {
    $gateway->execute(new Capture($token));

    if (false == isset($_REQUEST['noinvalidate'])) {
        $payum->getRequestVerifier()->invalidate($token);
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




This is how you can create a capture url.

```php
<?php

include config.php;

$token = $payum->getTokenFactory()->createCaptureToken($gatewayName, $details, 'afterCaptureUrl');

header("Location: ".$token->getTargetUrl());
```

Back to [scripts](https://github.com/Payum/Core/tree/master/Resources/docs/scripts/index.md).
Back to [index](https://github.com/Payum/Core/tree/master/Resources/docs/index.md).
