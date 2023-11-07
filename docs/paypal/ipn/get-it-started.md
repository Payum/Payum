# Get Started

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

{% hint style="warning" %}
**Warning:**

Important: After you receive the VERIFIED message, there are several important checks you must perform before you can assume that the message is legitimate and not already processed.
{% endhint %}

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/Payum)
