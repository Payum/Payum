<?php

namespace Payum\Paypal\ProCheckout\Nvp\Tests\Action;

use ArrayObject;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\Refund;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ProCheckout\Nvp\Action\RefundAction;
use Payum\Paypal\ProCheckout\Nvp\Api;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use stdClass;

class RefundActionTest extends GenericActionTest
{
    protected $actionClass = RefundAction::class;

    protected $requestClass = Refund::class;

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new ReflectionClass(RefundAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowIfUnsupportedApiGiven()
    {
        $this->expectException(UnsupportedApiException::class);
        $action = new RefundAction();

        $action->setApi(new stdClass());
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

        $action->execute(new Refund([]));
    }

    public function testThrowIfTransactionTypeIsNotRefundable()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You cannot refund transaction with type notSupported. Only these types could be refunded: S, D, F');
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('doCredit')
        ;

        $action = new RefundAction();
        $action->setApi($apiMock);

        $action->execute(new Refund([
            'RESULT' => Api::RESULT_SUCCESS,
            'TRXTYPE' => 'notSupported',
        ]));
    }

    public function testThrowIfTransactionIdNotSet()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The PNREF fields are required.');
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('doCredit')
        ;

        $action = new RefundAction();
        $action->setApi($apiMock);

        $action->execute(new Refund([
            'RESULT' => Api::RESULT_SUCCESS,
            'TRXTYPE' => Api::TRXTYPE_SALE,
        ]));
    }

    public function testShouldSetPnrefAsOriginIdAndPerformCreditApiCall()
    {
        $details = new ArrayObject([
            'RESULT' => Api::RESULT_SUCCESS,
            'TRXTYPE' => Api::TRXTYPE_SALE,
            'PNREF' => 'aRef',
        ]);

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('doCredit')
            ->with([
                'RESULT' => null,
                'TRXTYPE' => null,
                'PNREF' => 'aRef',
                'PURCHASE_TRXTYPE' => 'S',
                'PURCHASE_RESULT' => 0,
                'ORIGID' => 'aRef',
            ])
            ->willReturn([
                'RESULT' => 'aResult',
                'FOO' => 'aVal',
            ])
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
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }
}
