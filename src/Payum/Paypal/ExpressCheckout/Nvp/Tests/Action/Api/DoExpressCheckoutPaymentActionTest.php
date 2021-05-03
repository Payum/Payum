<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoExpressCheckoutPaymentAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPayment;

class DoExpressCheckoutPaymentActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(DoExpressCheckoutPaymentAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApoAwareInterface()
    {
        $rc = new \ReflectionClass(DoExpressCheckoutPaymentAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldSupportDoExpressCheckoutPaymentRequestAndArrayAccessAsModel()
    {
        $action = new DoExpressCheckoutPaymentAction();

        $this->assertTrue($action->supports(new DoExpressCheckoutPayment($this->createMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotDoExpressCheckoutPaymentRequest()
    {
        $action = new DoExpressCheckoutPaymentAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new DoExpressCheckoutPaymentAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfTokenNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('TOKEN must be set. Have you run SetExpressCheckoutAction?');
        $action = new DoExpressCheckoutPaymentAction();

        $action->execute(new DoExpressCheckoutPayment(array()));
    }

    /**
     * @test
     */
    public function throwIfPayerIdNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('PAYERID must be set.');
        $action = new DoExpressCheckoutPaymentAction();

        $request = new DoExpressCheckoutPayment(array(
            'TOKEN' => 'aToken',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function throwIfZeroPaymentRequestActionNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('PAYMENTREQUEST_0_PAYMENTACTION must be set.');
        $action = new DoExpressCheckoutPaymentAction();

        $request = new DoExpressCheckoutPayment(array(
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function throwIfZeroPaymentRequestAmtNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('PAYMENTREQUEST_0_AMT must be set.');
        $action = new DoExpressCheckoutPaymentAction();

        $request = new DoExpressCheckoutPayment(array(
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'anAction',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiDoExpressCheckoutMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doExpressCheckoutPayment')
            ->will($this->returnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TOKEN', $fields);
                $testCase->assertEquals('theToken', $fields['TOKEN']);

                $testCase->assertArrayHasKey('PAYMENTREQUEST_0_AMT', $fields);
                $testCase->assertEquals('theAmt', $fields['PAYMENTREQUEST_0_AMT']);

                $testCase->assertArrayHasKey('PAYMENTREQUEST_0_PAYMENTACTION', $fields);
                $testCase->assertEquals('theAction', $fields['PAYMENTREQUEST_0_PAYMENTACTION']);

                $testCase->assertArrayHasKey('PAYERID', $fields);
                $testCase->assertEquals('thePayerId', $fields['PAYERID']);

                return array();
            }))
        ;

        $action = new DoExpressCheckoutPaymentAction();
        $action->setApi($apiMock);

        $request = new DoExpressCheckoutPayment(array(
            'TOKEN' => 'theToken',
            'PAYERID' => 'thePayerId',
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'theAction',
            'PAYMENTREQUEST_0_AMT' => 'theAmt',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiDoExpressCheckoutMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doExpressCheckoutPayment')
            ->will($this->returnCallback(function () {
                return array(
                    'FIRSTNAME' => 'theFirstname',
                    'EMAIL' => 'the@example.com',
                );
            }))
        ;

        $action = new DoExpressCheckoutPaymentAction();
        $action->setApi($apiMock);

        $request = new DoExpressCheckoutPayment(array(
            'TOKEN' => 'aToken',
            'PAYERID' => 'aPayerId',
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'anAction',
            'PAYMENTREQUEST_0_AMT' => 'anAmt',
        ));

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('FIRSTNAME', $model);
        $this->assertEquals('theFirstname', $model['FIRSTNAME']);

        $this->assertArrayHasKey('EMAIL', $model);
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
