# How to capture?

```php
<?php
use Payum\Request\CaptureRequest;
use Payum\AuthorizeNet\Aim\PaymentFactory;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;

$authorizeNet = new AuthorizeNetAIM($apiLoginId = 'xxx', $transactionKey = 'xxx');
$authorizeNet->setSandbox(true);

$payment = PaymentFactory::create($authorizeNet);

$payment->execute($captureRequest = new CaptureRequest(array(
  'amount' => 10,
  'card_num' => '1234123412341234',
  'exp_date' => '10-02',
)));
```

