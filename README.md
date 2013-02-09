Authorize.Net AIM
=================

Simple sell page example:
```php
<?php
$vendorDir = "xxx";
$apiLoginId = "xxx";
$transactionKey = 'xxx';

require_once $vendorDir.'/ajbdev/authorizenet-php-api/AuthorizeNet.php';
require_once $vendorDir.'/vendor/autoload.php';

use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\AuthorizeNet\Aim\Payment;
use Payum\AuthorizeNet\Aim\Action\StatusAction;
use Payum\AuthorizeNet\Aim\Action\CaptureAction;
use Payum\Request\CaptureRequest;
use Payum\AuthorizeNet\Aim\PaymentInstruction;
use Payum\Request\BinaryMaskStatusRequest;

$authorizeNet = new AuthorizeNetAIM($apiLoginId, $transactionKey);
$authorizeNet->setSandbox(true);

$payment = new Payment($authorizeNet);
$payment->addAction(new CaptureAction());
$payment->addAction(new StatusAction());

$instruction = new PaymentInstruction;
$instruction->setCardNum($_REQUEST['card_num']);
$instruction->setExpDate($_REQUEST['exp_date']);
$instruction->setAmount($sell->getPrice());

if ($interactiveRequest = $payment->execute(new CaptureRequest($instruction))) {
    throw $interactiveRequest;
}

$statusRequest = new BinaryMaskStatusRequest($instruction);
if ($interactiveRequest = $payment->execute($statusRequest)) {
    throw $interactiveRequest;
}

var_dump('Success: '.(int) $statusRequest->isSuccess());
var_dump('New: '.(int) $statusRequest->isNew());
var_dump('Canceled: '.(int) $statusRequest->isCanceled());
var_dump('Failed: '.(int) $statusRequest->isFailed());
var_dump('In progress: '.(int) $statusRequest->isInProgress());
var_dump('Unknown: '.(int) $statusRequest->isUnknown());
```