<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# Sofort. Disable notifications

When you are working locally with Sofort you may get 

```
<?xml version="1.0" encoding="UTF-8"?>
<errors>
  <error>
    <code>8016</code>
    <message>Must be a valid URL.</message>
    <field>notification_urls.notification_url.1</field>
  </error>
</errors>
```

That's because the notification URL you sent to Sofort is not reachable and Sofort returns error in this case.
To work around the problem you can disable notifications by setting the additional option:

```php
<?php
namespace Acme;

use Payum\Sofort\SofortGatewayFactory;

$factory = new SofortGatewayFactory();

$gateway = $factory->create([
    'config_key' => 'aKey',
    'disable_notification' => true,
]);
```

and in Symfony:

```yaml
payum:
  gateways:
    sofort:
      config_key: 'aKey',
      disable_notification: true            
      factory: 'sofort'
```

Pay attention that you must do it only for local/dev environments and never in production.

Back to [index](../index.md).