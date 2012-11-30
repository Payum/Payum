<?php
ini_set('display_errors', 1);
error_reporting(-1);

$vendorDir = "xxx";

require_once $vendorDir.'/ajbdev/authorizenet-php-api/AuthorizeNet.php';
require_once $vendorDir.'/vendor/autoload.php';

use Payum\Request\Storage\FilesystemRequestStorage;

$storage = new FilesystemRequestStorage(sys_get_temp_dir(), 'Payum\Request\SimpleSellRequest', 'id');

if (false == empty($_POST)) {
    $sell = $storage->findRequestById($_GET['requestId']);
    $sell->getInstruction()->setCardNum($_REQUEST['card_num']);
    $sell->getInstruction()->setExpDate($_REQUEST['exp_date']);

    $storage->updateRequest($sell);

    header('Location: /authorize/sell.php?requestId='.$sell->getId());
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