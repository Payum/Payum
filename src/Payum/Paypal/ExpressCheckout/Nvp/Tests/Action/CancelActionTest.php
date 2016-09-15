<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Sync;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CancelAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoVoid;

class CancelActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(CancelAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(CancelAction::class);

        $this->assertTrue($rc->isSubclassOf(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CancelAction();
    }

    /**
     * @test
     */
    public function shouldSupportEmptyModel()
    {
        $action = new CancelAction();

        $request = new Cancel([]);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportCancelRequestWithArrayAsModelWhichHasPendingReasonAsAuthorized()
    {
        $action = new CancelAction();

        $payment = array(
           'PAYMENTINFO_0_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
        );

        $request = new Cancel($payment);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportCancelRequestWithArrayAsModelWhichHasPendingReasonAsOtherThanAuthorized()
    {
        $action = new CancelAction();

        $payment = array(
           'PAYMENTINFO_0_PENDINGREASON' => 'Foo',
        );

        $request = new Cancel($payment);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportCancelRequestWithNoArrayAccessAsModel()
    {
        $action = new CancelAction();

        $request = new Cancel(new \stdClass());

        $this->assertFalse($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCancelRequest()
    {
        $action = new CancelAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CancelAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldNotExecuteDoVoidIfTransactionIdNotSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new CancelAction();
        $action->setGateway($gatewayMock);

        $request = new Cancel([]);

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldExecuteDoVoidIfTransactionIdSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                array($this->isInstanceOf(DoVoid::class)),
                array($this->isInstanceOf(Sync::class))
            )
        ;

        $action = new CancelAction();
        $action->setGateway($gatewayMock);

        $request = new Cancel(array(
            'TRANSACTIONID' => 'theId',
        ));

        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock(GatewayInterface::class);
    }
}
