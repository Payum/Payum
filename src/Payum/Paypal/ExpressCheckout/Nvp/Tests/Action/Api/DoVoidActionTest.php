<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoVoidAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoVoid;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetails;

class DoVoidActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass(DoVoidAction::class);

        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
    }

    /**
     * @test
     */
    public function shouldImplementsGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(DoVoidAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new DoVoidAction();
    }

    /**
     * @test
     */
    public function shouldSupportDoVoidRequestAndArrayAccessAsModel()
    {
        $action = new DoVoidAction();

        $this->assertTrue($action->supports(new DoVoid(new \ArrayObject(), 0)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotDoVoidRequest()
    {
        $action = new DoVoidAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new DoVoidAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The AUTHORIZATIONID fields are required.
     */
    public function throwIfTransactionIdNorAuthorizationIdNotSetInModel()
    {
        $action = new DoVoidAction();

        $action->execute(new DoVoid([], 0));
    }

    /**
     * @test
     */
    public function shouldCallApiDoVoidMethodWithExpectedRequiredArguments()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('DoVoid')
            ->will($this->returnCallback(function (array $fields) {
                $this->assertArrayHasKey('AUTHORIZATIONID', $fields);
                $this->assertEquals('theParentTransactionId', $fields['AUTHORIZATIONID']);

                return array();
            }))
        ;

        $action = new DoVoidAction();
        $action->setApi($apiMock);
        $action->setGateway($this->createGatewayMock());

        $request = new DoVoid(array(
            'PAYMENTREQUEST_0_TRANSACTIONID' => 'theTransactionId',
            'PAYMENTREQUEST_0_PARENTTRANSACTIONID' => 'theParentTransactionId',
        ), 0);

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiDoVoidMethodWithParentTransactionIdMissing()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('DoVoid')
            ->will($this->returnCallback(function (array $fields) {
                $this->assertArrayHasKey('AUTHORIZATIONID', $fields);
                $this->assertEquals('theTransactionId', $fields['AUTHORIZATIONID']);

                return array();
            }))
        ;

        $action = new DoVoidAction();
        $action->setApi($apiMock);
        $action->setGateway($this->createGatewayMock());

        $request = new DoVoid(array(
            'PAYMENTREQUEST_0_TRANSACTIONID' => 'theTransactionId',
        ), 0);

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiDoVoidMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('DoVoid')
            ->will($this->returnCallback(function () {
                return array(
                    'FIRSTNAME' => 'theFirstname',
                    'EMAIL' => 'the@example.com',
                );
            }))
        ;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetTransactionDetails::class))
            ->will($this->returnCallback(function(GetTransactionDetails $request) {
                $this->assertSame(0, $request->getPaymentRequestN());
                $this->assertSame(array(
                    'PAYMENTREQUEST_0_TRANSACTIONID' => 'theTransactionId',
                ), (array) $request->getModel());


                $model = $request->getModel();
                $model['FIRSTNAME'] = 'theFirstname';
                $model['EMAIL'] = 'the@example.com';
            }))
        ;

        $action = new DoVoidAction();
        $action->setApi($apiMock);
        $action->setGateway($gatewayMock);

        $request = new DoVoid(array(
            'PAYMENTREQUEST_0_TRANSACTIONID' => 'theTransactionId',
        ), 0);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('FIRSTNAME', $model);
        $this->assertEquals('theFirstname', $model['FIRSTNAME']);

        $this->assertArrayHasKey('EMAIL', $model);
        $this->assertEquals('the@example.com', $model['EMAIL']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->getMock(Api::class, [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock(GatewayInterface::class);
    }
}
