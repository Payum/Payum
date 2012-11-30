<?php
ini_set('display_errors', 1);
error_reporting(-1);

$vendorDir = "xxx";
$apiLoginId = "xxx"; 
$transactionKey = 'xxx';

require_once $vendorDir.'/ajbdev/authorizenet-php-api/AuthorizeNet.php';
require_once $vendorDir.'/vendor/autoload.php';

use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\AuthorizeNet\Aim\Payment;
use Payum\AuthorizeNet\Aim\Action\SimpleSellAction;
use Payum\AuthorizeNet\Aim\Action\StatusAction;
use Payum\AuthorizeNet\Aim\Action\AuthorizeAndCaptureAction;
use Payum\AuthorizeNet\Aim\Request\Instruction;
use Payum\Request\Storage\FilesystemRequestStorage;
use Payum\Request\UserInputRequiredInteractiveRequest;
use Payum\Request\BinaryMaskStatusRequest;

$storage = new FilesystemRequestStorage(sys_get_temp_dir(), 'Payum\Request\SimpleSellRequest', 'id');

$authorizeNet = new AuthorizeNetAIM($apiLoginId, $transactionKey);
$authorizeNet->setSandbox(true);

$payment = new Payment;
$payment->addAction(new AuthorizeAndCaptureAction($authorizeNet));
$payment->addAction(new SimpleSellAction());
$payment->addAction(new StatusAction());

if (array_key_exists('requestId', $_GET)) {
    $sell = $storage->findRequestById($_GET['requestId']);
} else {
    $sell = $storage->createRequest();
    $sell->setPrice(100);
    $sell->setCurrency('EUR');
    $sell->setInstruction(new Instruction);

    $storage->updateRequest($sell);
}

$interactiveRequest = $payment->execute($sell);
if ($interactiveRequest instanceof UserInputRequiredInteractiveRequest) {
    header('Location: /authorize/user-input.php?requestId='.$sell->getId());
    exit;
}

$statusRequest = new BinaryMaskStatusRequest($sell);
$payment->execute($statusRequest);

var_dump('Success: '.(int) $statusRequest->isSuccess());
var_dump('New: '.(int) $statusRequest->isNew());
var_dump('Canceled: '.(int) $statusRequest->isCanceled());
var_dump('Failed: '.(int) $statusRequest->isFailed());
var_dump('In progress: '.(int) $statusRequest->isInProgress());
var_dump('Unknown: '.(int) $statusRequest->isUnknown());