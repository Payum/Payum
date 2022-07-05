<?php

namespace Payum\Paypal\Rest\Tests\Action;

use Iterator;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\Rest\Action\ConvertAction;
use stdClass;

class ConvertActionTest extends GenericActionTest
{
    protected $actionClass = ConvertAction::class;

    protected $requestClass = Convert::class;

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

    public function testShouldCorrectlyConvertOrderToDetailsAndSetItBack()
    {
        $payment = new Payment();
        $payment->setNumber('theNumber');
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setClientId('theClientId');
        $payment->setClientEmail('theClientEmail');

        $action = new ConvertAction();

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getAfterUrl')
            ->willReturn('https://example.com');

        $action->execute($convert = new Convert($payment, 'array', $token));

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

        $this->assertArrayHasKey('client_email', $details);
        $this->assertSame('theClientEmail', $details['client_email']);

        $this->assertArrayHasKey('client_id', $details);
        $this->assertSame('theClientId', $details['client_id']);

        $this->assertArrayHasKey('return_url', $details);
        $this->assertSame('https://example.com', $details['return_url']);
    }

    public function testShouldNotOverwriteAlreadySetExtraDetails()
    {
        $payment = new Payment();
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setDetails([
            'foo' => 'fooVal',
        ]);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getAfterUrl')
            ->willReturn('https://example.com');

        $action = new ConvertAction();

        $action->execute($convert = new Convert($payment, 'array', $token));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertSame('fooVal', $details['foo']);
    }
}
