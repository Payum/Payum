<?php
namespace Payum\AuthorizeNet\Aim\Tests\Functional;

use Payum\Request\CaptureRequest;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\AuthorizeNet\Aim\PaymentFactory;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;

class ExecuteTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @test
   */
  public function doCapture()
  {
    if (false == isset($GLOBALS['__PAYUM_AUTHORIZE_NET_AIM_API_LOGIN_ID'])) {
      $this->markTestSkipped('Please configure __PAYUM_AUTHORIZE_NET_AIM_API_LOGIN_ID in your phpunit.xml');
    }
    if (false == isset($GLOBALS['__PAYUM_AUTHORIZE_NET_AIM_API_TRANSACTION_KEY'])) {
      $this->markTestSkipped('Please configure __PAYUM_AUTHORIZE_NET_AIM_API_TRANSACTION_KEY in your phpunit.xml');
    }

    $authorizeNet = new AuthorizeNetAIM(
      $GLOBALS['__PAYUM_AUTHORIZE_NET_AIM_API_LOGIN_ID'],
      $GLOBALS['__PAYUM_AUTHORIZE_NET_AIM_API_TRANSACTION_KEY']
    );
    $authorizeNet->setSandbox(true);

    $payment = PaymentFactory::create($authorizeNet);

    $payment->execute($captureRequest = new CaptureRequest(array(
        'amount' => 10,
        'card_num' => '4111111111111111',
        'exp_date' => date('Y') . '-' . date('n'),
        'duplicate_window' => 1, // prevents "A duplicate transaction has been submitted." error
      )));

    $statusRequest = new BinaryMaskStatusRequest($captureRequest->getModel());
    $payment->execute($statusRequest);

    $model = $statusRequest->getModel();
    if (!$statusRequest->isSuccess()) {
      $this->fail($model['response_reason_text']);
    }
    $this->assertNotEmpty($model['transaction_id']);
  }
}
