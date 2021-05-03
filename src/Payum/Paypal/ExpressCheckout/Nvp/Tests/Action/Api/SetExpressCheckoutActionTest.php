<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\SetExpressCheckoutAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckout;

class SetExpressCheckoutActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(SetExpressCheckoutAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApoAwareInterface()
    {
        $rc = new \ReflectionClass(SetExpressCheckoutAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldSupportSetExpressCheckoutRequestAndArrayAccessAsModel()
    {
        $action = new SetExpressCheckoutAction();

        $request = new SetExpressCheckout($this->createMock('ArrayAccess'));

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotSetExpressCheckoutRequest()
    {
        $action = new SetExpressCheckoutAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new SetExpressCheckoutAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfModelNotHavePaymentAmountSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The PAYMENTREQUEST_0_AMT must be set.');
        $action = new SetExpressCheckoutAction();

        $request = new SetExpressCheckout(new \ArrayObject());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiGetExpressCheckoutDetailsMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $expectedAmount = 154.23;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('setExpressCheckout')
            ->will($this->returnCallback(function (array $fields) use ($testCase, $expectedAmount) {
                $testCase->assertArrayHasKey('PAYMENTREQUEST_0_AMT', $fields);
                $testCase->assertEquals($expectedAmount, $fields['PAYMENTREQUEST_0_AMT']);

                return array();
            }))
        ;

        $action = new SetExpressCheckoutAction($apiMock);
        $action->setApi($apiMock);

        $request = new SetExpressCheckout(array(
            'PAYMENTREQUEST_0_AMT' => $expectedAmount,
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiDoExpressCheckoutMethodAndUpdateInstructionFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('setExpressCheckout')
            ->will($this->returnCallback(function () {
                return array(
                    'FIRSTNAME' => 'theFirstname',
                    'EMAIL' => 'the@example.com',
                );
            }))
        ;

        $action = new SetExpressCheckoutAction();
        $action->setApi($apiMock);

        $request = new SetExpressCheckout(array(
            'PAYMENTREQUEST_0_AMT' => $expectedAmount = 154.23,
        ));

        $action->execute($request);

        $model = $request->getModel();

        $this->assertEquals('theFirstname', $model['FIRSTNAME']);
        $this->assertEquals('the@example.com', $model['EMAIL']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
