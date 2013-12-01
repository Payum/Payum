<?php
namespace Payum\Payex\Tests\Action;

use Payum\PaymentInterface;
use Payum\Request\SyncRequest;
use Payum\Payex\Action\PaymentDetailsSyncAction;

class PaymentDetailsSyncActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\PaymentDetailsSyncAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaymentDetailsSyncAction;
    }

    /**
     * @test
     */
    public function shouldSupportSyncRequestWithArrayAccessAsModelIfTransactionNumberSet()
    {
        $action = new PaymentDetailsSyncAction();

        $array = $this->getMock('ArrayAccess');
        $array
            ->expects($this->at(0))
            ->method('offsetExists')
            ->with('transactionNumber')
            ->will($this->returnValue(true))
        ;

        $this->assertTrue($action->supports(new SyncRequest($array)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotSyncRequest()
    {
        $action = new PaymentDetailsSyncAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportSyncRequestWithNotArrayAccessModel()
    {
        $action = new PaymentDetailsSyncAction;

        $this->assertFalse($action->supports(new SyncRequest(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new PaymentDetailsSyncAction;

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldDoSubExecuteCheckOrderApiRequest()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Payex\Request\Api\CheckOrderRequest'))
        ;
        
        $action = new PaymentDetailsSyncAction();
        $action->setPayment($paymentMock);

        $action->execute(new SyncRequest(array(
            'transactionNumber' => 'aNum'
        )));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\PaymentInterface');
    }
}