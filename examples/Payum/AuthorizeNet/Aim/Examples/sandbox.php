<?php
require_once __DIR__.'/../vendor/ajbdev/authorizenet-php-api/AuthorizeNet.php';
require_once __DIR__.'/../vendor/autoload.php';

ini_set('display_errors', 1);
error_reporting(-1);

$authorizeNet = new \Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM($apiLoginId = "9W84J799eu3v",$transactionKey = '6U843s99kEgEAt5Z');
$authorizeNet->setSandbox(true);

$payment = new \Payum\AuthorizeNet\Aim\Payment();

$payment->addAction(new \Payum\AuthorizeNet\Aim\Action\AuthorizeAndCaptureAction(
    $authorizeNet, 
    '/authorize-net-aim.php?input_credit_card=1'
));
$payment->addAction(new \Payum\AuthorizeNet\Aim\Action\SimpleSellAction());
$payment->addAction(new \Payum\AuthorizeNet\Aim\Action\StatusAction());

if (isset($_GET['start'])) {
    if ($_GET['start'] == 2) {
        $sell = unserialize(file_get_contents(__DIR__.'/request'));
    } else {
        $sell = new \Payum\Request\SimpleSellRequest();
        $sell->setPrice(100);
        $sell->setCurrency('EUR');
        $sell->setInstruction(new \Payum\AuthorizeNet\Aim\Request\Instruction());

        file_put_contents(__DIR__.'/request', serialize($sell));
    }

    $interactiveRequest = $payment->execute($sell);
    if ($interactiveRequest instanceof \Payum\Request\RedirectUrlInteractiveRequest) {
        header('Location: '.$interactiveRequest->getUrl());
        exit;
    }

    $statusRequest = new \Payum\Request\BinaryMaskStatusRequest($sell);
    $payment->execute($statusRequest);

    var_dump('Success: '.(int) $statusRequest->isSuccess());
    var_dump('New: '.(int) $statusRequest->isNew());
    var_dump('Canceled: '.(int) $statusRequest->isCanceled());
    var_dump('Failed: '.(int) $statusRequest->isFailed());
    var_dump('In progress: '.(int) $statusRequest->isInProgress());
    var_dump('Unknown: '.(int) $statusRequest->isUnknown());
    die;
}

if (isset($_GET['input_credit_card'])) {
    $sell = unserialize(file_get_contents(__DIR__.'/request'));
    if (empty($_POST)) {
        echo '<form method="POST">';
        echo 'Card number: <input type="text" name="card_num" value="">';
        echo '<br />';
        echo 'Exp date: <input type="text" name="exp_date" value="">';
        echo '<br />';
        echo '<input type="submit" value="Submit">';
        echo '</form>';
        
        exit;
    } else {
        $sell->getInstruction()->setCardNum($_REQUEST['card_num']);
        $sell->getInstruction()->setExpDate($_REQUEST['exp_date']);

        file_put_contents(__DIR__.'/request', serialize($sell));

        header('Location: /authorize-net-aim.php?start=2');
        exit;
    }
}

var_dump('fuck!!!');
die;