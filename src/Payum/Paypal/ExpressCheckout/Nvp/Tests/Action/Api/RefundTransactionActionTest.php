<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\RefundTransactionAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\RefundTransaction;
use PHPUnit\Framework\MockObject\MockObject;

class RefundTransactionActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\RefundTransactionAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\RefundTransactionAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\ApiAwareInterface'));
    }

    public function testShouldUseApiAwareTrait()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\RefundTransactionAction');

        $this->assertContains('Payum\Core\ApiAwareTrait', $rc->getTraitNames());
    }

    public function testShouldSupportRefundTransactionRequestAndArrayAccessAsModel()
    {
        $action = new RefundTransactionAction();

        $this->assertTrue(
            $action->supports(new RefundTransaction($this->createMock('ArrayAccess')))
        );
    }

    public function testShouldNotSupportAnythingNotRefundTransactionRequest()
    {
        $action = new RefundTransactionAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new RefundTransactionAction();

        $action->execute(new \stdClass());
    }

    public function testThrowIfAuthorizationIdNotSetInModel()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The TRANSACTIONID fields are required.');

        $action = new RefundTransactionAction();

        $request = new RefundTransaction(array());

        $action->execute($request);
    }

    public function testShouldCallApiRefundTransactionMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('RefundTransaction')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TRANSACTIONID', $fields);
                $testCase->assertSame('theOriginalTransactionId', $fields['TRANSACTIONID']);

                return array();
            })
        ;

        $action = new RefundTransactionAction();
        $action->setApi($apiMock);

        $request = new RefundTransaction(array(
            'TRANSACTIONID' => 'theOriginalTransactionId',
        ));

        $action->execute($request);
    }

    public function testShouldCallApiRefundTransactionMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('RefundTransaction')
            ->willReturnCallback(function () {
                return array(
                    'TRANSACTIONID' => 'theTransactionId',
                    'REFUNDTRANSACTIONID' => 'theRefundTransactionId',
                );
            })
        ;

        $action = new RefundTransactionAction();
        $action->setApi($apiMock);

        $request = new RefundTransaction(array(
            'TRANSACTIONID' => 'theTransactionId',
        ));

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('TRANSACTIONID', $model);
        $this->assertSame('theTransactionId', $model['TRANSACTIONID']);

        $this->assertArrayHasKey('REFUNDTRANSACTIONID', $model);
        $this->assertSame('theRefundTransactionId', $model['REFUNDTRANSACTIONID']);
    }

    /**
     * @return MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
