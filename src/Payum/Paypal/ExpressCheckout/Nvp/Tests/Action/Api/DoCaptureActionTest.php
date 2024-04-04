<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoCaptureAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoCapture;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetails;

class DoCaptureActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(DoCaptureAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface()
    {
        $rc = new \ReflectionClass(DoCaptureAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldImplementsGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(DoCaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportDoCaptureRequestAndArrayAccessAsModel()
    {
        $action = new DoCaptureAction();

        $this->assertTrue($action->supports(new DoCapture(new \ArrayObject(), 0)));
    }

    public function testShouldNotSupportAnythingNotDoCaptureRequest()
    {
        $action = new DoCaptureAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new DoCaptureAction();

        $action->execute(new \stdClass());
    }

    public function testThrowIfTransactionIdNorAuthorizationIdNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The AMT, COMPLETETYPE, AUTHORIZATIONID fields are required.');
        $action = new DoCaptureAction();

        $action->execute(new DoCapture([], 0));
    }

    public function testThrowIfCompleteTypeNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The COMPLETETYPE fields are required.');
        $action = new DoCaptureAction();

        $request = new DoCapture(array(
            'PAYMENTREQUEST_0_TRANSACTIONID' => 'aTransactionId',
            'PAYMENTREQUEST_0_AMT' => 100,
        ), 0);

        $action->execute($request);
    }

    public function testThrowIfAmtNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The AMT fields are required.');
        $action = new DoCaptureAction();

        $request = new DoCapture(array(
            'PAYMENTREQUEST_0_TRANSACTIONID' => 'aReferenceId',
            'PAYMENTREQUEST_0_COMPLETETYPE' => 'Complete',
        ), 0);

        $action->execute($request);
    }

    public function testShouldCallApiDoCaptureMethodWithExpectedRequiredArguments()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('DoCapture')
            ->willReturnCallback(function (array $fields) {
                $this->assertArrayHasKey('TRANSACTIONID', $fields);
                $this->assertSame('theTransactionId', $fields['TRANSACTIONID']);

                $this->assertArrayHasKey('AMT', $fields);
                $this->assertSame('theAmt', $fields['AMT']);

                $this->assertArrayHasKey('COMPLETETYPE', $fields);
                $this->assertSame('Complete', $fields['COMPLETETYPE']);

                return array();
            })
        ;

        $action = new DoCaptureAction();
        $action->setApi($apiMock);
        $action->setGateway($this->createGatewayMock());

        $request = new DoCapture(array(
            'PAYMENTREQUEST_0_TRANSACTIONID' => 'theTransactionId',
            'PAYMENTREQUEST_0_COMPLETETYPE' => 'Complete',
            'PAYMENTREQUEST_0_AMT' => 'theAmt',
        ), 0);

        $action->execute($request);
    }

    public function testShouldCallApiDoCaptureMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('DoCapture')
            ->willReturnCallback(function () {
                return array(
                    'FIRSTNAME' => 'theFirstname',
                    'EMAIL' => 'the@example.com',
                );
            })
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetTransactionDetails::class))
            ->willReturnCallback(function (GetTransactionDetails $request) {
                $this->assertSame(0, $request->getPaymentRequestN());
                $this->assertSame(array(
                    'PAYMENTREQUEST_0_TRANSACTIONID' => 'theTransactionId',
                    'PAYMENTREQUEST_0_COMPLETETYPE' => 'Complete',
                    'PAYMENTREQUEST_0_AMT' => 'theAmt',
                ), (array) $request->getModel());


                $model = $request->getModel();
                $model['FIRSTNAME'] = 'theFirstname';
                $model['EMAIL'] = 'the@example.com';
            })
        ;

        $action = new DoCaptureAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new DoCapture(array(
            'PAYMENTREQUEST_0_TRANSACTIONID' => 'theTransactionId',
            'PAYMENTREQUEST_0_COMPLETETYPE' => 'Complete',
            'PAYMENTREQUEST_0_AMT' => 'theAmt',
        ), 0);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('FIRSTNAME', $model);
        $this->assertSame('theFirstname', $model['FIRSTNAME']);

        $this->assertArrayHasKey('EMAIL', $model);
        $this->assertSame('the@example.com', $model['EMAIL']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class, [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
