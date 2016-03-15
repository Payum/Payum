<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Model\CreditCard;
use Payum\Core\Model\Payment;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\Generic;
use Payum\Core\Security\SensitiveValue;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\Stripe\Action\ConvertPaymentAction;

class ConvertPaymentActionTest extends GenericActionTest
{
    protected $actionClass = ConvertPaymentAction::class;

    protected $requestClass = Convert::class;

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(new Payment(), 'array')),
            array(new $this->requestClass($this->getMock(PaymentInterface::class), 'array')),
            array(new $this->requestClass(new Payment(), 'array', $this->getMock(TokenInterface::class))),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass(Generic::class, array(array()))),
            array(new $this->requestClass(new \stdClass(), 'array')),
            array(new $this->requestClass(new Payment(), 'foobar')),
            array(new $this->requestClass($this->getMock(PaymentInterface::class), 'foobar')),
        );
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertOrderToDetailsAndSetItBack()
    {
        $order = new Payment();
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');

        $action = new ConvertPaymentAction();

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayNotHasKey('card', $details);

        $this->assertArrayHasKey('amount', $details);
        $this->assertEquals(123, $details['amount']);

        $this->assertArrayHasKey('currency', $details);
        $this->assertEquals('USD', $details['currency']);

        $this->assertArrayHasKey('description', $details);
        $this->assertEquals('the description', $details['description']);
    }

    /**
     * @test
     */
    public function shouldNotOverwriteAlreadySetExtraDetails()
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
        $this->assertEquals('fooVal', $details['foo']);
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertCreditCard()
    {
        $creditCard = new CreditCard();
        $creditCard->setNumber('4111111111111111');
        $creditCard->setExpireAt(new \DateTime('2018-05-12'));
        $creditCard->setSecurityCode(123);
        $creditCard->setHolder('John Doe');

        $order = new Payment();
        $order->setCreditCard($creditCard);

        $action = new ConvertPaymentAction();

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('card', $details);
        $this->assertInstanceOf(SensitiveValue::class, $details['card']);

        $card = $details['card']->peek();
        $this->assertInternalType('array', $card);

        $this->assertArrayHasKey('number', $card);
        $this->assertEquals('4111111111111111', $card['number']);

        $this->assertArrayHasKey('exp_month', $card);
        $this->assertEquals('05', $card['exp_month']);

        $this->assertArrayHasKey('exp_year', $card);
        $this->assertEquals('2018', $card['exp_year']);

        $this->assertArrayHasKey('cvc', $card);
        $this->assertEquals('123', $card['cvc']);
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertCreditCardToken()
    {
        $creditCard = new CreditCard();
        $creditCard->setToken('theCustomerId');

        $order = new Payment();
        $order->setCreditCard($creditCard);

        $action = new ConvertPaymentAction();

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('customer', $details);
        $this->assertEquals('theCustomerId', $details['customer']);
    }
}
