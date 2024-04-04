<?php
namespace Payum\Be2Bill\Tests\Action;

use Payum\Be2Bill\Action\ConvertPaymentAction;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\Token;
use Payum\Core\Request\Convert;
use Payum\Core\Tests\GenericActionTest;

class ConvertPaymentActionTest extends GenericActionTest
{
    protected $actionClass = ConvertPaymentAction::class;

    protected $requestClass = Convert::class;

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

    public function testShouldCorrectlyConvertOrderToDetailsAndSetItBack()
    {
        $payment = new Payment();
        $payment->setNumber('theNumber');
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setClientId('theClientId');
        $payment->setClientEmail('theClientEmail');

        $action = new ConvertPaymentAction();

        $action->execute($convert = new Convert($payment, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('AMOUNT', $details);
        $this->assertSame(123, $details['AMOUNT']);

        $this->assertArrayHasKey('ORDERID', $details);
        $this->assertSame('theNumber', $details['ORDERID']);

        $this->assertArrayHasKey('DESCRIPTION', $details);
        $this->assertSame('the description', $details['DESCRIPTION']);

        $this->assertArrayHasKey('CLIENTIDENT', $details);
        $this->assertSame('theClientId', $details['CLIENTIDENT']);

        $this->assertArrayHasKey('CLIENTEMAIL', $details);
        $this->assertSame('theClientEmail', $details['CLIENTEMAIL']);
    }

    public function testShouldNotOverwriteAlreadySetExtraDetails()
    {
        $payment = new Payment();
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setDetails(array(
            'foo' => 'fooVal',
        ));

        $action = new ConvertPaymentAction();

        $action->execute($convert = new Convert($payment, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertSame('fooVal', $details['foo']);
    }
}
