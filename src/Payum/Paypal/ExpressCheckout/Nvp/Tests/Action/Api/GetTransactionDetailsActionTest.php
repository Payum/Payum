<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetTransactionDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetails;

class GetTransactionDetailsActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(GetTransactionDetailsAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface()
    {
        $rc = new \ReflectionClass(GetTransactionDetailsAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportGetTransactionDetailsRequestAndArrayAccessAsModel()
    {
        $action = new GetTransactionDetailsAction();

        $request = new GetTransactionDetails($this->createMock('ArrayAccess'), $paymentRequestN = 5);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotGetTransactionDetailsRequest()
    {
        $action = new GetTransactionDetailsAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new GetTransactionDetailsAction();

        $action->execute(new \stdClass());
    }

    public function testThrowIfZeroPaymentRequestTransactionIdNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('PAYMENTREQUEST_5_TRANSACTIONID must be set.');
        $action = new GetTransactionDetailsAction();

        $request = new GetTransactionDetails(array(), $paymentRequestN = 5);

        $action->execute($request);
    }

    public function testShouldCallApiGetTransactionDetailsMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getTransactionDetails')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TRANSACTIONID', $fields);
                $testCase->assertSame('theTransactionId', $fields['TRANSACTIONID']);

                return array();
            })
        ;

        $action = new GetTransactionDetailsAction();
        $action->setApi($apiMock);

        $request = new GetTransactionDetails(array(
            'PAYMENTREQUEST_5_TRANSACTIONID' => 'theTransactionId',
        ), $paymentRequestN = 5);

        $action->execute($request);
    }

    public function testShouldCallApiGetTransactionDetailsAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getTransactionDetails')
            ->willReturnCallback(function () {
                return array(
                    'PAYMENTSTATUS' => 'theStatus',
                );
            })
        ;

        $action = new GetTransactionDetailsAction();
        $action->setApi($apiMock);

        $request = new GetTransactionDetails(array(
            'PAYMENTREQUEST_5_TRANSACTIONID' => 'aTransactionId',
        ), $paymentRequestN = 5);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('PAYMENTREQUEST_5_PAYMENTSTATUS', $model);
        $this->assertSame('theStatus', $model['PAYMENTREQUEST_5_PAYMENTSTATUS']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
