<?php
require_once __DIR__.'/../vendor/ajbdev/authorizenet-php-api/AuthorizeNet.php';
require_once __DIR__.'/../vendor/autoload.php';

ini_set('display_errors', 1);
error_reporting(-1);

$authorizeNet = new \Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM(
    $apiLoginId = "9W84J799eu3v",
    $transactionKey = '6U843s99kEgEAt5Z'
);
$authorizeNet->setSandbox(true);

$payment = new \Payum\AuthorizeNet\Aim\Payment();
$payment->addAction(new \Payum\AuthorizeNet\Aim\Action\AuthorizeAndCaptureAction($authorizeNet));
$payment->addAction(new \Payum\AuthorizeNet\Aim\Action\SimpleSellAction());
$payment->addAction(new \Payum\AuthorizeNet\Aim\Action\StatusAction());

if (isset($_GET['sell-new'])) {
    $sell = new \Payum\Request\SimpleSellRequest();
    $sell->setPrice(100);
    $sell->setCurrency('EUR');
    $sell->setInstruction(new \Payum\AuthorizeNet\Aim\Request\Instruction());

    file_put_contents(__DIR__.'/request', serialize($sell));
    $_GET['sell']=1;
}

if (isset($_GET['sell'])) {
    $sell = unserialize(file_get_contents(__DIR__.'/request'));

    $interactiveRequest = $payment->execute($sell);
    if ($interactiveRequest instanceof \Payum\Request\UserInputRequiredInteractiveRequest) {
        header('Location: /authorize-net-aim.php?input-required=1');
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

if (isset($_GET['input-required'])) {
    $sell = unserialize(file_get_contents(__DIR__.'/request'));
    if (false == empty($_POST)) {
        $sell->getInstruction()->setCardNum($_REQUEST['card_num']);
        $sell->getInstruction()->setExpDate($_REQUEST['exp_date']);

        file_put_contents(__DIR__.'/request', serialize($sell));

        header('Location: /authorize-net-aim.php?sell=1');
        exit;
    }

    echo '
        <form method="POST">
            Card number: <input type="text" name="card_num" value="">
            <br />
            Exp date: <input type="text" name="exp_date" value="">
            <br />
            
            <input type="submit" value="Submit">
        </form>
    ';

    exit;
}