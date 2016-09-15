<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

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
    public function shouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\CancelAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\GatewayAwareAction'));
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
    public function shouldSetZeroGatewayActionAsVoid()
    {
        $action = new CancelAction();
        $action->setGateway($this->createGatewayMock());

        $action->execute($request = new Cancel([]));

        $model = $request->getModel();
        $this->assertArrayHasKey('PAYMENTREQUEST_0_PAYMENTACTION', $model);
        $this->assertEquals(Api::PAYMENTACTION_VOID, $model['PAYMENTREQUEST_0_PAYMENTACTION']);
    }

    /**
     * @test
     */
    public function shouldForcePaymentActionVoid()
    {
        $action = new CancelAction();
        $action->setGateway($this->createGatewayMock());

        $action->execute($request = new Cancel([
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'FooBarBaz',
        ]));

        $model = $request->getModel();
        $this->assertArrayHasKey('PAYMENTREQUEST_0_PAYMENTACTION', $model);
        $this->assertEquals(Api::PAYMENTACTION_VOID, $model['PAYMENTREQUEST_0_PAYMENTACTION']);
    }

    /**
     * @test
     */
    public function shouldSupportEmptyModel()
    {
        $action = new CancelAction();

        $request = new Cancel(array());

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
    public function shouldNotExecuteDoVoidIfPaymentInfoPendingReasonNotSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new CancelAction();
        $action->setGateway($gatewayMock);

        $request = new Cancel(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldExecuteDoVoidIfPaymentInfoPendingReasonSet()
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
            'PAYMENTINFO_0_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldExecuteDoVoidForEachPaymentInfoPendingReasonSetIndexedToNine()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->exactly(4))
            ->method('execute')
            ->withConsecutive(
                array($this->isInstanceOf(DoVoid::class)),
                array($this->isInstanceOf(DoVoid::class)),
                array($this->isInstanceOf(DoVoid::class)),
                array($this->isInstanceOf(Sync::class))
            )
        ;

        $action = new CancelAction();
        $action->setGateway($gatewayMock);

        $request = new Cancel(array(
            'PAYMENTINFO_0_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
            'PAYMENTINFO_1_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
            'PAYMENTINFO_9_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldNotExecuteDoVoidForPaymentInfoPendingReasonIndexedOverNine()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new CancelAction();
        $action->setGateway($gatewayMock);

        $request = new Cancel(array(
            'PAYMENTINFO_10_PENDINGREASON' => Api::PENDINGREASON_AUTHORIZATION,
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldNotSetAuthorizationIdFromTransactionIdIfBothNotSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new CancelAction();
        $action->setGateway($gatewayMock);

        $request = new Cancel(array());

        $action->execute($request);

        $details = $request->getModel();

        $this->assertFalse(isset($details['AUTHORIZATIONID']));
    }

    /**
     * @test
     */
    public function shouldSetAuthorizationIdFromTransactionIdIfNotSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new CancelAction();
        $action->setGateway($gatewayMock);

        $request = new Cancel(array(
            'TRANSACTIONID' => 'theOriginalTransactionId',
        ));

        $action->execute($request);

        $details = $request->getModel();

        $this->assertEquals($details['AUTHORIZATIONID'], 'theOriginalTransactionId');
    }

    /**
     * @test
     */
    public function shouldNotOverrideAuthorizationIdWithTransactionIdIfSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new CancelAction();
        $action->setGateway($gatewayMock);

        $request = new Cancel(array(
            'TRANSACTIONID' => 'theReauthorizedTransactionId',
            'AUTHORIZATIONID' => 'theOriginalTransactionId',
        ));

        $action->execute($request);

        $details = $request->getModel();

        $this->assertEquals($details['AUTHORIZATIONID'], 'theOriginalTransactionId');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock('Payum\Core\GatewayInterface');
    }
}
