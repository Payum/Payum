<?php

namespace Payum\Offline\Tests\Action;

use Iterator;
use Payum\Core\Model\Payout;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\Offline\Action\ConvertPayoutAction;
use Payum\Offline\Constants;
use stdClass;

class ConvertPayoutActionTest extends GenericActionTest
{
    protected $actionClass = ConvertPayoutAction::class;

    protected $requestClass = Convert::class;

    public function provideSupportedRequests(): Iterator
    {
        yield [new $this->requestClass(new Payout(), 'array')];
        yield [new $this->requestClass($this->createMock(PayoutInterface::class), 'array')];
        yield [new $this->requestClass(new Payout(), 'array', $this->createMock(TokenInterface::class))];
    }

    public function provideNotSupportedRequests(): Iterator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new stdClass()];
        yield [$this->getMockForAbstractClass(Generic::class, [[]])];
        yield [new $this->requestClass(new stdClass(), 'array')];
        yield [new $this->requestClass(new Payout(), 'foobar')];
        yield [new $this->requestClass($this->createMock(PayoutInterface::class), 'foobar')];
    }

    public function testShouldCorrectlyConvertOrderToDetailsAndSetItBack()
    {
        $order = new Payout();
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setRecipientId('theRecipientId');
        $order->setRecipientEmail('theRecipientEmail');

        $action = new ConvertPayoutAction();

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('amount', $details);
        $this->assertSame(123, $details['amount']);

        $this->assertArrayHasKey('currency', $details);
        $this->assertSame('USD', $details['currency']);

        $this->assertArrayHasKey('description', $details);
        $this->assertSame('the description', $details['description']);

        $this->assertArrayHasKey('recipient_id', $details);
        $this->assertSame('theRecipientId', $details['recipient_id']);

        $this->assertArrayHasKey('recipient_email', $details);
        $this->assertSame('theRecipientEmail', $details['recipient_email']);

        $this->assertArrayHasKey(Constants::FIELD_PAYOUT, $details);
        $this->assertEquals(true, $details[Constants::FIELD_PAYOUT]);
    }

    public function testShouldForcePayedoutFalseIfAlreadySet()
    {
        $order = new Payout();
        $order->setDetails([
            Constants::FIELD_PAYOUT => false,
        ]);

        $action = new ConvertPayoutAction();

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey(Constants::FIELD_PAYOUT, $details);
        $this->assertEquals(false, $details[Constants::FIELD_PAYOUT]);
    }

    public function testShouldNotOverwriteAlreadySetExtraDetails()
    {
        $order = new Payout();
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setDetails([
            'foo' => 'fooVal',
        ]);

        $action = new ConvertPayoutAction();

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertSame('fooVal', $details['foo']);
    }
}
