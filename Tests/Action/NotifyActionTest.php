<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Core\Request\NotifyRequest;
use Payum\Core\Request\SyncRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Action\NotifyAction;

class NotifyActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\NotifyAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new NotifyAction();
    }

    /**
     * @test
     */
    public function shouldSupportNotifyRequestAndArrayAccessAsModel()
    {
        $action = new NotifyAction();

        $request = new NotifyRequest(array(), $this->getMock('ArrayAccess'));
        
        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotNotifyRequest()
    {
        $action = new NotifyAction();
        
        $request = new \stdClass();

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotifyRequestAndNotArrayAccessAsModel()
    {
        $action = new NotifyAction();
        
        $request = new NotifyRequest(array(), new \stdClass());
        
        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new NotifyAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSubExecuteSyncRequestWithSameModel()
    {
        $expectedModel = array('foo' => 'fooVal');

        $testCase = $this;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\SyncRequest'))
        ;
        
        $action = new NotifyAction();
        $action->setPayment($paymentMock);

        $action->execute(new NotifyRequest(array(), $expectedModel));
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }
}
