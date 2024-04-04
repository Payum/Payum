<?php
namespace Payum\AuthorizeNet\Aim\Tests\Action;

use Payum\AuthorizeNet\Aim\Action\ConvertPaymentAction;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Tests\GenericActionTest;

class ConvertPaymentActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\AuthorizeNet\Aim\Action\ConvertPaymentAction';

    protected $requestClass = 'Payum\Core\Request\Convert';

    public function provideSupportedRequests(): \Iterator
    {
        yield array(new $this->requestClass(new Payment(), 'array'));
        yield array(new $this->requestClass($this->createMock(PaymentInterface::class), 'array'));
        yield array(new $this->requestClass(new Payment(), 'array', $this->createMock('Payum\Core\Security\TokenInterface')));
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array())));
        yield array(new $this->requestClass(new \stdClass(), 'array'));
        yield array(new $this->requestClass(new Payment(), 'foobar'));
        yield array(new $this->requestClass($this->createMock(PaymentInterface::class), 'foobar'));
    }

    public function testShouldCorrectlyConvertPaymentToArray()
    {
        $gatewayMock = $this->createMock('Payum\Core\GatewayInterface');
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetCurrency'))
            ->willReturnCallback(function (GetCurrency $request) {
                $request->name = 'US Dollar';
                $request->alpha3 = 'USD';
                $request->numeric = 123;
                $request->exp = 2;
                $request->country = 'US';
            })
        ;

        $payment = new Payment();
        $payment->setNumber('theNumber');
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setClientId('theClientId');
        $payment->setClientEmail('theClientEmail');

        $action = new ConvertPaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($convert = new Convert($payment, 'array'));

        $result = $convert->getResult();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $this->assertArrayHasKey('amount', $result);
        $this->assertEqualsWithDelta(1.23, $result['amount'], PHP_FLOAT_EPSILON);

        $this->assertArrayHasKey('invoice_num', $result);
        $this->assertSame('theNumber', $result['invoice_num']);

        $this->assertArrayHasKey('description', $result);
        $this->assertSame('the description', $result['description']);

        $this->assertArrayHasKey('cust_id', $result);
        $this->assertSame('theClientId', $result['cust_id']);

        $this->assertArrayHasKey('email', $result);
        $this->assertSame('theClientEmail', $result['email']);
    }

    public function testShouldNotOverwriteAlreadySetExtraDetails()
    {
        $gatewayMock = $this->createMock('Payum\Core\GatewayInterface');
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetCurrency'))
            ->willReturnCallback(function (GetCurrency $request) {
                $request->name = 'US Dollar';
                $request->alpha3 = 'USD';
                $request->numeric = 123;
                $request->exp = 2;
                $request->country = 'US';
            })
        ;

        $payment = new Payment();
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setDetails(array(
            'foo' => 'fooVal',
        ));

        $action = new ConvertPaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($convert = new Convert($payment, 'array'));

        $result = $convert->getResult();

        $this->assertNotEmpty($result);

        $this->assertArrayHasKey('foo', $result);
        $this->assertSame('fooVal', $result['foo']);
    }
}
