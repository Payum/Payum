<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Action\Api;

use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ProHosted\Nvp\Action\Api\GetTransactionDetailsAction;
use Payum\Paypal\ProHosted\Nvp\Request\Api\GetTransactionDetails;

class GetTransactionDetailsActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementsApiAwareAction()
    {
        $rc = new \ReflectionClass(GetTransactionDetailsAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportGetTransactionDetailsRequestAndArrayAccessAsModel()
    {
        $action = new GetTransactionDetailsAction();

        $request = new GetTransactionDetails($this->createMock('ArrayAccess'));

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
        $this->expectExceptionMessage('TRANSACTIONID must be set.');
        $action = new GetTransactionDetailsAction();

        $request = new GetTransactionDetails(array());

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
            });

        $action = new GetTransactionDetailsAction();
        $action->setApi($apiMock);

        $request = new GetTransactionDetails(array(
            'txn_id' => 'aTransactionId',
        ));

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('PAYMENTSTATUS', $model);
        $this->assertSame('theStatus', $model['PAYMENTSTATUS']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ProHosted\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ProHosted\Nvp\Api', array(), array(), '', false);
    }
}
