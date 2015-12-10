<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Payum\Core\Request\Sync;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsSyncAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetExpressCheckoutDetails;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetails;

class PaymentDetailsSyncActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsSyncAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\GatewayAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaymentDetailsSyncAction();
    }

    /**
     * @test
     */
    public function shouldSupportSyncAndArrayAsModelWhichHasPaymentRequestAmountSet()
    {
        $action = new PaymentDetailsSyncAction();

        $paymentDetails = array(
            'PAYMENTREQUEST_0_AMT' => 12,
        );

        $request = new Sync($paymentDetails);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportSyncAndArrayAsModelWhichHasPaymentRequestAmountSetToZero()
    {
        $action = new PaymentDetailsSyncAction();

        $paymentDetails = array(
            'PAYMENTREQUEST_0_AMT' => 0,
        );

        $request = new Sync($paymentDetails);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotSync()
    {
        $action = new PaymentDetailsSyncAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new PaymentDetailsSyncAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldDoNothingIfTokenNotSet()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new PaymentDetailsSyncAction();
        $action->setGateway($gatewayMock);

        $request = new Sync(array(
            'PAYMENTREQUEST_0_AMT' => 12,
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldRequestGetExpressCheckoutDetailsAndUpdateModelIfTokenSetInModel()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetExpressCheckoutDetails'))
            ->will($this->returnCallback(function (GetExpressCheckoutDetails $request) {
                $model = $request->getModel();
                $model['foo'] = 'fooVal';
                $model['PAYMENTREQUEST_0_AMT'] = 33;
            }))
        ;

        $action = new PaymentDetailsSyncAction();
        $action->setGateway($gatewayMock);

        $details = new \ArrayObject(array(
            'PAYMENTREQUEST_0_AMT' => 11,
            'TOKEN' => 'aToken',
        ));

        $action->execute($sync = new Sync($details));

        $this->assertArrayHasKey('foo', (array) $details);
        $this->assertEquals('fooVal', $details['foo']);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_AMT', (array) $details);
        $this->assertEquals(33, $details['PAYMENTREQUEST_0_AMT']);
    }

    /**
     * @test
     */
    public function shouldRequestGetExpressCheckoutDetailsAndDoNotUpdateModelIfSessionExpired()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetExpressCheckoutDetails'))
            ->will($this->returnCallback(function (GetExpressCheckoutDetails $request) {
                $model = $request->getModel();
                $model['foo'] = 'fooVal';
                $model['PAYMENTREQUEST_0_AMT'] = 33;
                $model['L_ERRORCODE0'] = Api::L_ERRORCODE_SESSION_HAS_EXPIRED;
            }))
        ;

        $action = new PaymentDetailsSyncAction();
        $action->setGateway($gatewayMock);

        $details = new \ArrayObject(array(
            'PAYMENTREQUEST_0_AMT' => 11,
            'TOKEN' => 'aToken',
        ));

        $action->execute($sync = new Sync($details));

        $this->assertArrayNotHasKey('foo', (array) $details);

        $this->assertArrayHasKey('PAYMENTREQUEST_0_AMT', (array) $details);
        $this->assertEquals(11, $details['PAYMENTREQUEST_0_AMT']);
    }

    /**
     * @test
     */
    public function shouldRequestGetTransactionDetailsTwice()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetails'))
            ->will($this->returnCallback(function (GetTransactionDetails $request) {
                $model = $request->getModel();
                $model['foo'] = 'fooVal';
            }))
        ;
        $gatewayMock
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetails'))
            ->will($this->returnCallback(function (GetTransactionDetails $request) {
                $model = $request->getModel();
                $model['bar'] = 'barVal';
            }))
        ;

        $action = new PaymentDetailsSyncAction();
        $action->setGateway($gatewayMock);

        $details = new \ArrayObject(array(
            'PAYMENTREQUEST_0_AMT' => 12,
            'TOKEN' => 'aToken',
            'PAYMENTREQUEST_0_TRANSACTIONID' => 'zeroTransId',
            'PAYMENTREQUEST_9_TRANSACTIONID' => 'nineTransId',
        ));

        $action->execute(new Sync($details));

        $this->assertArrayHasKey('foo', (array) $details);
        $this->assertEquals('fooVal', $details['foo']);

        $this->assertArrayHasKey('bar', (array) $details);
        $this->assertEquals('barVal', $details['bar']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock('Payum\Core\GatewayInterface');
    }
}
