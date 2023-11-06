<?php
namespace Payum\Klarna\Checkout\Tests\Action;

use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\Generic;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\Klarna\Checkout\Action\ConvertPaymentAction;

class ConvertPaymentActionTest extends GenericActionTest
{
    protected $actionClass = ConvertPaymentAction::class;

    protected $requestClass = Convert::class;

    public function provideSupportedRequests(): \Iterator
    {
        yield array(new $this->requestClass(new Payment(), 'array'));
        yield array(new $this->requestClass($this->createMock(PaymentInterface::class), 'array'));
        yield array(new $this->requestClass(new Payment(), 'array', $this->createMock(TokenInterface::class)));
    }

    public function provideNotSupportedRequests(): \Iterator
    {
        yield array('foo');
        yield array(array('foo'));
        yield array(new \stdClass());
        yield array($this->getMockForAbstractClass(Generic::class, array(array())));
        yield array(new $this->requestClass(new \stdClass(), 'array'));
        yield array(new $this->requestClass(new Payment(), 'foobar'));
        yield array(new $this->requestClass($this->createMock(PaymentInterface::class), 'foobar'));
    }

    public function testShouldCorrectlyConvertOrderToDetailsAndSetItBack()
    {
        $payment = new Payment();
        $payment->setNumber('theNumber');
        $payment->setCurrencyCode('SEK');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setClientId('theClientId');
        $payment->setClientEmail('theClientEmail');

        $action = new ConvertPaymentAction();

        $action->execute($convert = new Convert($payment, 'array'));

        $details = $convert->getResult();

        $this->assertSame([
            'cart' => [
                'items' => [
                    [
                        'reference' => 'theNumber',
                        'name' => 'theNumber',
                        'quantity' => 1,
                        'unit_price' => 164.0,
                        'discount_rate' => 0,
                        'tax_rate' => 2500,
                    ]
                ],
            ],
            'purchase_country' => 'SE',
            'purchase_currency' => 'SEK',
            'locale' => 'sv-se',

        ], $details);
    }

    public function testShouldDoNothingIfCurrencyNotSEK()
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

        $this->assertSame([], $details);
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
