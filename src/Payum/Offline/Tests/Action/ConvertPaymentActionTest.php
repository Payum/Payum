<?php

namespace Payum\Offline\Tests\Action;

use Iterator;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\Offline\Action\ConvertPaymentAction;
use Payum\Offline\Constants;
use stdClass;

class ConvertPaymentActionTest extends GenericActionTest
{
    /**
     * @var class-string<ConvertPaymentAction>
     */
    protected $actionClass = ConvertPaymentAction::class;

    /**
     * @var class-string<Convert>
     */
    protected $requestClass = Convert::class;

    /**
     * @return \Iterator<Convert[]>
     */
    public function provideSupportedRequests(): Iterator
    {
        yield [new $this->requestClass(new Payment(), 'array')];
        yield [new $this->requestClass($this->createMock(PaymentInterface::class), 'array')];
        yield [new $this->requestClass(new Payment(), 'array', $this->createMock(TokenInterface::class))];
    }

    public function provideNotSupportedRequests(): Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new stdClass()];
        yield [$this->getMockForAbstractClass(Generic::class, [[]])];
        yield [new $this->requestClass(new stdClass(), 'array')];
        yield [new $this->requestClass(new Payment(), 'foobar')];
        yield [new $this->requestClass($this->createMock(PaymentInterface::class), 'foobar')];
    }

    public function testShouldCorrectlyConvertOrderToDetailsAndSetItBack(): void
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

    public function testShouldForcePaidFalseIfAlreadySet(): void
    {
        $order = new Payment();
        $order->setDetails([
            Constants::FIELD_PAID => false,
        ]);

        $action = new ConvertPaymentAction();

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey(Constants::FIELD_PAID, $details);
        $this->assertEquals(false, $details[Constants::FIELD_PAID]);
    }

    public function testShouldNotOverwriteAlreadySetExtraDetails(): void
    {
        $order = new Payment();
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setDetails([
            'foo' => 'fooVal',
        ]);

        $action = new ConvertPaymentAction();

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertSame('fooVal', $details['foo']);
    }
}
