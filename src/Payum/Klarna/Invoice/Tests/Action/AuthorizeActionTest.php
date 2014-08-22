<?php
namespace Payum\Klarna\Invoice\Tests\Action;

use Payum\Core\PaymentInterface;
use Payum\Core\Request\Authorize;
use Payum\Klarna\Invoice\Action\AuthorizeAction;

class AuthorizeActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\AuthorizeAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new AuthorizeAction;
    }

    /**
     * @test
     */
    public function shouldSupportAuthorizeWithArrayAsModel()
    {
        $action = new AuthorizeAction();

        $this->assertTrue($action->supports(new Authorize(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotAuthorize()
    {
        $action = new AuthorizeAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportAuthorizeWithNotArrayAccessModel()
    {
        $action = new AuthorizeAction;

        $this->assertFalse($action->supports(new Authorize(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new AuthorizeAction;

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSubExecuteReserveAmountIfRnoNotSet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Invoice\Request\Api\ReserveAmount'))
        ;

        $action = new AuthorizeAction;
        $action->setPayment($paymentMock);

        $request = new Authorize(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldNotSubExecuteReserveAmountIfRnoAlreadySet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new AuthorizeAction;
        $action->setPayment($paymentMock);

        $request = new Authorize(array(
            'rno' => 'aRno',
        ));

        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }
}