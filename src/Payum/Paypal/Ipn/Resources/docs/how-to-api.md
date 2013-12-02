# How to Api?

```php
<?php
use Buzz\Client\Curl;
use Payum\Paypal\Ipn\Api;

$api = new Api(new Curl, array(
    'sandbox' => true
)));

if (Api::NOTIFY_VERIFIED === $api->notifyValidate($_POST)) {
    echo 'It is valid paypal notification. Let\'s do some additional checks';
}

echo 'Something wrong in notification';
```

**Warning:**

> Important: After you receive the VERIFIED message, there are several important checks you must perform before you can assume that the message is legitimate and not already processed.

