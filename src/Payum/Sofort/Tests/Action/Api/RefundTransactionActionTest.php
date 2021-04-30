<?php

namespace Payum\Sofort\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Sofort\Action\Api\RefundTransactionAction;
use Payum\Sofort\Request\Api\RefundTransaction;
use Payum\Sofort\Api;

class RefundTransactionActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(RefundTransactionAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(RefundTransactionAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldSupportRefundTransactionRequestWithArrayAccessAsModel()
    {
        $action = new RefundTransactionAction();

        $this->assertTrue($action->supports(new RefundTransaction($this->createMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingRefundTransactionRequest()
    {
        $action = new RefundTransactionAction($this->createApiMock());

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new RefundTransactionAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfTransactionIdParameterIsNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The parameter "transaction_id" must be set. Have you run CreateTransactionAction?');
        $action = new RefundTransactionAction();

        $request = new RefundTransaction(array());
        $action->execute($request);
    }

    /**
     * @test
     */
    public function throwIfAmountParameterIsNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('One of the parameters "refund_amount" or "amount" must be set.');
        $action = new RefundTransactionAction();

        $request = new RefundTransaction(array('transaction_id' => 'daTransactionId'));
        $action->execute($request);
    }

    /**
     * @test
     */
    public function shoulUseAmountAsRefundAmountIfNotSet()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('refundTransaction')
            ->will($this->returnCallback(function ($details) {
                $this->assertEquals(100, $details['refund_amount']);

                return array();
            }));

        $action = new RefundTransactionAction();
        $action->setApi($apiMock);

        $request = new RefundTransaction(array(
            'transaction_id' => 'sweetTransactionId',
            'amount' => 100,
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shoulUseRefundAmountIfAmountSet()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('refundTransaction')
            ->will($this->returnCallback(function ($details) {
                $this->assertEquals(50, $details['refund_amount']);

                return array();
            }));

        $action = new RefundTransactionAction();
        $action->setApi($apiMock);

        $request = new RefundTransaction(array(
            'transaction_id' => 'sweetTransactionId',
            'amount' => 100,
            'refund_amount' => 50,
        ));

        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Sofort\Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }
}
