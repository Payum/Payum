<?php

namespace Payum\Sofort\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Sofort\Action\Api\RefundTransactionAction;
use Payum\Sofort\Request\Api\RefundTransaction;
use Payum\Sofort\Api;

class RefundTransactionActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(RefundTransactionAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(RefundTransactionAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testShouldSupportRefundTransactionRequestWithArrayAccessAsModel()
    {
        $action = new RefundTransactionAction();

        $this->assertTrue($action->supports(new RefundTransaction($this->createMock('ArrayAccess'))));
    }

    public function testShouldNotSupportAnythingRefundTransactionRequest()
    {
        $action = new RefundTransactionAction($this->createApiMock());

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new RefundTransactionAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    public function testThrowIfTransactionIdParameterIsNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The parameter "transaction_id" must be set. Have you run CreateTransactionAction?');
        $action = new RefundTransactionAction();

        $request = new RefundTransaction(array());
        $action->execute($request);
    }

    public function testThrowIfAmountParameterIsNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('One of the parameters "refund_amount" or "amount" must be set.');
        $action = new RefundTransactionAction();

        $request = new RefundTransaction(array('transaction_id' => 'daTransactionId'));
        $action->execute($request);
    }

    public function testShoulUseAmountAsRefundAmountIfNotSet()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('refundTransaction')
            ->willReturnCallback(function ($details) {
                $this->assertSame(100, $details['refund_amount']);

                return array();
            });

        $action = new RefundTransactionAction();
        $action->setApi($apiMock);

        $request = new RefundTransaction(array(
            'transaction_id' => 'sweetTransactionId',
            'amount' => 100,
        ));

        $action->execute($request);
    }

    public function testShoulUseRefundAmountIfAmountSet()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('refundTransaction')
            ->willReturnCallback(function ($details) {
                $this->assertSame(50, $details['refund_amount']);

                return array();
            });

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
