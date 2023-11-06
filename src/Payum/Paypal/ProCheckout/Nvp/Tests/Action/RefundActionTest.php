<?php
namespace Payum\Paypal\ProCheckout\Nvp\Tests\Action;

use Payum\Core\Request\Refund;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ProCheckout\Nvp\Api;
use Payum\Paypal\ProCheckout\Nvp\Action\RefundAction;

class RefundActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\Paypal\ProCheckout\Nvp\Action\RefundAction';

    protected $requestClass = 'Payum\Core\Request\Refund';

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ProCheckout\Nvp\Action\RefundAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    public function testThrowIfUnsupportedApiGiven()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $action = new RefundAction();

        $action->setApi(new \stdClass());
    }

    public function testShouldDoNothingIfPaymentNew()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('doCredit')
        ;

        $action = new RefundAction();
        $action->setApi($apiMock);

        $action->execute(new Refund(array()));
    }

    public function testThrowIfTransactionTypeIsNotRefundable()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('You cannot refund transaction with type notSupported. Only these types could be refunded: S, D, F');
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('doCredit')
        ;

        $action = new RefundAction();
        $action->setApi($apiMock);

        $action->execute(new Refund(array(
            'RESULT' => Api::RESULT_SUCCESS,
            'TRXTYPE' => 'notSupported',
        )));
    }

    public function testThrowIfTransactionIdNotSet()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The PNREF fields are required.');
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('doCredit')
        ;

        $action = new RefundAction();
        $action->setApi($apiMock);

        $action->execute(new Refund(array(
            'RESULT' => Api::RESULT_SUCCESS,
            'TRXTYPE' => Api::TRXTYPE_SALE,
        )));
    }

    public function testShouldSetPnrefAsOriginIdAndPerformCreditApiCall()
    {
        $details = new \ArrayObject(array(
            'RESULT' => Api::RESULT_SUCCESS,
            'TRXTYPE' => Api::TRXTYPE_SALE,
            'PNREF' => 'aRef',
        ));

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doCredit')
            ->with(array(
                'RESULT' => null,
                'TRXTYPE' => null,
                'PNREF' => 'aRef',
                'PURCHASE_TRXTYPE' => 'S',
                'PURCHASE_RESULT' => 0,
                'ORIGID' => 'aRef',
            ))
            ->willReturn(array(
                'RESULT' => 'aResult',
                'FOO' => 'aVal',
            ))
        ;

        $action = new RefundAction();
        $action->setApi($apiMock);

        $action->execute(new Refund($details));

        $this->assertArrayHasKey('FOO', $details);
        $this->assertSame('aVal', $details['FOO']);

        $this->assertArrayHasKey('RESULT', $details);
        $this->assertSame('aResult', $details['RESULT']);

        $this->assertArrayHasKey('ORIGID', $details);
        $this->assertSame('aRef', $details['ORIGID']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ProCheckout\Nvp\Api', array(), array(), '', false);
    }
}
