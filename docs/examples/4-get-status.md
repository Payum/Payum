# 3. Get gateway. 

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

Back to [examples](index.md).
Back to [index](../index.md).
