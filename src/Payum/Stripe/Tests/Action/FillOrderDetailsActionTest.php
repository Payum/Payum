<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Model\CreditCard;
use Payum\Core\Model\Order;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Tests\GenericActionTest;
use Payum\Stripe\Action\FillOrderDetailsAction;

class FillOrderDetailsActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\Stripe\Action\FillOrderDetailsAction';

    protected $requestClass = 'Payum\Core\Request\FillOrderDetails';

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(new Order())),
            array(new $this->requestClass($this->getMock('Payum\Core\Model\OrderInterface'))),
            array(new $this->requestClass(new Order(), $this->getMock('Payum\Core\Security\TokenInterface'))),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array()))),
        );
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertOrderToDetailsAndSetItBack()
    {
        $order = new Order();
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');

        $action = new FillOrderDetailsAction();

        $action->execute(new FillOrderDetails($order));

        $details = $order->getDetails();

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
        $order = new Order();
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setDetails(array(
            'foo' => 'fooVal',
        ));

        $action = new FillOrderDetailsAction();

        $action->execute(new FillOrderDetails($order));

        $details = $order->getDetails();

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

        $order = new Order();
        $order->setCreditCard($creditCard);

        $action = new FillOrderDetailsAction();

        $action->execute(new FillOrderDetails($order));

        $details = $order->getDetails();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('card', $details);
        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $details['card']);

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
}
