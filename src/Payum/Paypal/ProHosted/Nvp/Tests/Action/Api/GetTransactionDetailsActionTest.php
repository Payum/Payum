<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Action\Api;

use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ProHosted\Nvp\Action\Api\GetTransactionDetailsAction;
use Payum\Paypal\ProHosted\Nvp\Request\Api\GetTransactionDetails;

class GetTransactionDetailsActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementsApiAwareAction()
    {
        $rc = new \ReflectionClass(GetTransactionDetailsAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldSupportGetTransactionDetailsRequestAndArrayAccessAsModel()
    {
        $action = new GetTransactionDetailsAction();

        $request = new GetTransactionDetails($this->createMock('ArrayAccess'));

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotGetTransactionDetailsRequest()
    {
        $action = new GetTransactionDetailsAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new GetTransactionDetailsAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfZeroPaymentRequestTransactionIdNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('TRANSACTIONID must be set.');
        $action = new GetTransactionDetailsAction();

        $request = new GetTransactionDetails(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiGetTransactionDetailsAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getTransactionDetails')
            ->will($this->returnCallback(function () {
                return array(
                    'PAYMENTSTATUS' => 'theStatus',
                );
            }));

        $action = new GetTransactionDetailsAction();
        $action->setApi($apiMock);

        $request = new GetTransactionDetails(array(
            'txn_id' => 'aTransactionId',
        ));

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('PAYMENTSTATUS', $model);
        $this->assertEquals('theStatus', $model['PAYMENTSTATUS']);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Payum\Paypal\ProHosted\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ProHosted\Nvp\Api', array(), array(), '', false);
    }
}
