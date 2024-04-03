# Get Status

```php
<?php

use Payum\Core\GatewayInterface;
use Payum\Core\Model\Payment;
use Payum\Core\Request\GetHumanStatus;

/** @var array|\ArrayObject|Payment $model */

/** @var GatewayInterface $gateway */
$gateway->execute($status = new GetHumanStatus($model));

$status->isNew();
$status->isPending();
$status->isAuthorized();
$status->isCaptured();
$status->isRefunded();
$status->isCanceled();
$status->isSuspended();
$status->isFailed();
$status->isUnknown();

$status->getValue(); // 'new', 'authorized', 'captured' and so on.
```

Back to [examples](index.md)

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/Payum)
