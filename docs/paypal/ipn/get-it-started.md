# Paypal IPN. Get it started.

```php
<?php
use Payum\Paypal\Ipn\Api;

/** @var \Payum\Core\HttpClientInterface $client */ 
/** @var \Http\Message\MessageFactory $messageFactory */

$api = new Api(['sandbox' => true], $client, $messageFactory);

if (Api::NOTIFY_VERIFIED === $api->notifyValidate($_POST)) {
    echo 'It is valid paypal notification. Let\'s do some additional checks';
}

echo 'Something wrong in notification';
```

**Warning:**

> Important: After you receive the VERIFIED message, there are several important checks you must perform before you can assume that the message is legitimate and not already processed.

Back to [index](../../index.md).
