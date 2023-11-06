<?php
namespace Payum\Offline\Tests\Action;

use Payum\Core\Model\PaymentInterface;
use Payum\Offline\Action\ConvertPaymentAction;
use Payum\Core\Model\Payment;
use Payum\Core\Request\Convert;
use Payum\Core\Tests\GenericActionTest;
use Payum\Offline\Constants;

class ConvertPaymentActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\Offline\Action\ConvertPaymentAction';

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

    public function testShouldCorrectlyConvertOrderToDetailsAndSetItBack()
    {
        $order = new Payment();
        $order->setNumber('theNumber');
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setClientId('theClientId');
        $order->setClientEmail('theClientEmail');

        $action = new ConvertPaymentAction();

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('amount', $details);
        $this->assertSame(123, $details['amount']);

        $this->assertArrayHasKey('currency', $details);
        $this->assertSame('USD', $details['currency']);

        $this->assertArrayHasKey('number', $details);
        $this->assertSame('theNumber', $details['number']);

        $this->assertArrayHasKey('description', $details);
        $this->assertSame('the description', $details['description']);

        $this->assertArrayHasKey('client_id', $details);
        $this->assertSame('theClientId', $details['client_id']);

        $this->assertArrayHasKey('client_email', $details);
        $this->assertSame('theClientEmail', $details['client_email']);

        $this->assertArrayHasKey(Constants::FIELD_PAID, $details);
        $this->assertEquals(true, $details[Constants::FIELD_PAID]);
    }

    public function testShouldForcePaidFalseIfAlreadySet()
    {
        $order = new Payment();
        $order->setDetails(array(
            Constants::FIELD_PAID => false,
        ));

        $action = new ConvertPaymentAction();

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey(Constants::FIELD_PAID, $details);
        $this->assertEquals(false, $details[Constants::FIELD_PAID]);
    }

    public function testShouldNotOverwriteAlreadySetExtraDetails()
    {
        $order = new Payment();
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setDetails(array(
            'foo' => 'fooVal',
        ));

        $action = new ConvertPaymentAction();

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertSame('fooVal', $details['foo']);
    }
}
