<?php

namespace Payum\Sofort\Tests\Action\Api;

use Payum\Sofort\Action\Api\RefundTransactionAction;
use Payum\Sofort\Request\Api\RefundTransaction;
use Payum\Sofort\Action\Api\BaseApiAwareAction;
use Payum\Sofort\Api;

class RefundTransactionActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass(RefundTransactionAction::class);

        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArgument()
    {
        new RefundTransactionAction();
    }

    /**
     * @test
     */
    public function shouldSupportRefundTransactionRequestWithArrayAccessAsModel()
    {
        $action = new RefundTransactionAction();

        $this->assertTrue($action->supports(new RefundTransaction($this->getMock('ArrayAccess'))));
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
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new RefundTransactionAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The parameter "transaction_id" must be set. Have you run CreateTransactionAction?
     */
    public function throwIfTransactionIdParameterIsNotSet()
    {
        $action = new RefundTransactionAction();

        $request = new RefundTransaction(array());
        $action->execute($request);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage One of the parameters "refund_amount" or "amount" must be set.
     */
    public function throwIfAmountParameterIsNotSet()
    {
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
                \PHPUnit_Framework_Assert::assertEquals(100, $details['refund_amount']);

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
                \PHPUnit_Framework_Assert::assertEquals(50, $details['refund_amount']);

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
        return $this->getMock(Api::class, array(), array(), '', false);
    }
}
