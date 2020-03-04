<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\RefundTransactionAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\RefundTransaction;

class RefundTransactionActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\RefundTransactionAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\RefundTransactionAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldUseApiAwareTrait()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\RefundTransactionAction');

        $this->assertContains('Payum\Core\ApiAwareTrait', $rc->getTraitNames());
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new RefundTransactionAction();
    }

    /**
     * @test
     */
    public function shouldSupportRefundTransactionRequestAndArrayAccessAsModel()
    {
        $action = new RefundTransactionAction();

        $this->assertTrue(
            $action->supports(new RefundTransaction($this->createMock('ArrayAccess')))
        );
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotRefundTransactionRequest()
    {
        $action = new RefundTransactionAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new RefundTransactionAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The TRANSACTIONID fields are required.
     */
    public function throwIfAuthorizationIdNotSetInModel()
    {
        $action = new RefundTransactionAction();

        $request = new RefundTransaction(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiRefundTransactionMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('RefundTransaction')
            ->will($this->returnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TRANSACTIONID', $fields);
                $testCase->assertEquals('theOriginalTransactionId', $fields['TRANSACTIONID']);

                return array();
            }))
        ;

        $action = new RefundTransactionAction();
        $action->setApi($apiMock);

        $request = new RefundTransaction(array(
            'TRANSACTIONID' => 'theOriginalTransactionId',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiRefundTransactionMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('RefundTransaction')
            ->will($this->returnCallback(function () {
                return array(
                    'TRANSACTIONID' => 'theTransactionId',
                    'REFUNDTRANSACTIONID' => 'theRefundTransactionId',
                );
            }))
        ;

        $action = new RefundTransactionAction();
        $action->setApi($apiMock);

        $request = new RefundTransaction(array(
            'TRANSACTIONID' => 'theTransactionId',
        ));

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('TRANSACTIONID', $model);
        $this->assertEquals('theTransactionId', $model['TRANSACTIONID']);

        $this->assertArrayHasKey('REFUNDTRANSACTIONID', $model);
        $this->assertEquals('theRefundTransactionId', $model['REFUNDTRANSACTIONID']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
