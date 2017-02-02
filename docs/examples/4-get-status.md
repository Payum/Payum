# 3. Get gateway. 

```php
<?php

$gateway->execute($status = new \Payum\Core\Request\GetHumanStatus($model));

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