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

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ProCheckout\Nvp\Action\RefundAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createApiMock();

        $action = new RefundAction();
        $action->setApi($expectedApi);

        $this->assertAttributeSame($expectedApi, 'api', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwIfUnsupportedApiGiven()
    {
        $action = new RefundAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldDoNothingIfPaymentNew()
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

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage You cannot refund transaction with type notSupported. Only these types could be refunded: S, D, F
     */
    public function throwIfTransactionTypeIsNotRefundable()
    {
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

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The PNREF fields are required.
     */
    public function throwIfTransactionIdNotSet()
    {
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

    /**
     * @test
     */
    public function shouldSetPnrefAsOriginIdAndPerformCreditApiCall()
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
            ->will($this->returnValue(array(
                'RESULT' => 'aResult',
                'FOO' => 'aVal',
            )))
        ;

        $action = new RefundAction();
        $action->setApi($apiMock);

        $action->execute(new Refund($details));

        $this->assertTrue(isset($details['FOO']));
        $this->assertEquals('aVal', $details['FOO']);

        $this->assertTrue(isset($details['RESULT']));
        $this->assertEquals('aResult', $details['RESULT']);

        $this->assertTrue(isset($details['ORIGID']));
        $this->assertEquals('aRef', $details['ORIGID']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ProCheckout\Nvp\Api', array(), array(), '', false);
    }
}
