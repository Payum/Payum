<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoExpressCheckoutPaymentAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPayment;

class DoExpressCheckoutPaymentActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(DoExpressCheckoutPaymentAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface()
    {
        $rc = new \ReflectionClass(DoExpressCheckoutPaymentAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportDoExpressCheckoutPaymentRequestAndArrayAccessAsModel()
    {
        $action = new DoExpressCheckoutPaymentAction();

        $this->assertTrue($action->supports(new DoExpressCheckoutPayment($this->createMock('ArrayAccess'))));
    }

    public function testShouldNotSupportAnythingNotDoExpressCheckoutPaymentRequest()
    {
        $action = new DoExpressCheckoutPaymentAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new DoExpressCheckoutPaymentAction();

        $action->execute(new \stdClass());
    }

    public function testThrowIfTokenNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('TOKEN must be set. Have you run SetExpressCheckoutAction?');
        $action = new DoExpressCheckoutPaymentAction();

        $action->execute(new DoExpressCheckoutPayment(array()));
    }

    public function testThrowIfPayerIdNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('PAYERID must be set.');
        $action = new DoExpressCheckoutPaymentAction();

        $request = new DoExpressCheckoutPayment(array(
            'TOKEN' => 'aToken',
        ));

        $action->execute($request);
    }

    public function testThrowIfZeroPaymentRequestActionNotSet()
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

    public function testThrowIfZeroPaymentRequestAmtNotSet()
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

    public function testShouldCallApiDoExpressCheckoutMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doExpressCheckoutPayment')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TOKEN', $fields);
                $testCase->assertSame('theToken', $fields['TOKEN']);

                $testCase->assertArrayHasKey('PAYMENTREQUEST_0_AMT', $fields);
                $testCase->assertSame('theAmt', $fields['PAYMENTREQUEST_0_AMT']);

                $testCase->assertArrayHasKey('PAYMENTREQUEST_0_PAYMENTACTION', $fields);
                $testCase->assertSame('theAction', $fields['PAYMENTREQUEST_0_PAYMENTACTION']);

                $testCase->assertArrayHasKey('PAYERID', $fields);
                $testCase->assertSame('thePayerId', $fields['PAYERID']);

                return array();
            })
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

    public function testShouldCallApiDoExpressCheckoutMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doExpressCheckoutPayment')
            ->willReturnCallback(function () {
                return array(
                    'FIRSTNAME' => 'theFirstname',
                    'EMAIL' => 'the@example.com',
                );
            })
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
        $this->assertSame('theFirstname', $model['FIRSTNAME']);

        $this->assertArrayHasKey('EMAIL', $model);
        $this->assertSame('the@example.com', $model['EMAIL']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
