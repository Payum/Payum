<?php
namespace Payum\Offline\Tests\Action\Api;

use Payum\Core\Model\Payout;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Tests\GenericActionTest;
use Payum\Offline\Action\ConvertPayoutAction;
use Payum\Offline\Constants;

class ConvertPayoutActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\Offline\Action\ConvertPayoutAction';

    protected $requestClass = 'Payum\Core\Request\Convert';

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(new Payout(), 'array')),
            array(new $this->requestClass($this->createMock(PayoutInterface::class), 'array')),
            array(new $this->requestClass(new Payout(), 'array', $this->createMock('Payum\Core\Security\TokenInterface'))),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array()))),
            array(new $this->requestClass(new \stdClass(), 'array')),
            array(new $this->requestClass(new Payout(), 'foobar')),
            array(new $this->requestClass($this->createMock(PayoutInterface::class), 'foobar')),
        );
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertOrderToDetailsAndSetItBack()
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
        $this->assertEquals(123, $details['amount']);

        $this->assertArrayHasKey('currency', $details);
        $this->assertEquals('USD', $details['currency']);

        $this->assertArrayHasKey('description', $details);
        $this->assertEquals('the description', $details['description']);

        $this->assertArrayHasKey('recipient_id', $details);
        $this->assertEquals('theRecipientId', $details['recipient_id']);

        $this->assertArrayHasKey('recipient_email', $details);
        $this->assertEquals('theRecipientEmail', $details['recipient_email']);

        $this->assertArrayHasKey(Constants::FIELD_PAYOUT, $details);
        $this->assertEquals(true, $details[Constants::FIELD_PAYOUT]);
    }

    /**
     * @test
     */
    public function shouldForcePayedoutFalseIfAlreadySet()
    {
        $order = new Payout();
        $order->setDetails(array(
            Constants::FIELD_PAYOUT => false,
        ));

        $action = new ConvertPayoutAction();

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey(Constants::FIELD_PAYOUT, $details);
        $this->assertEquals(false, $details[Constants::FIELD_PAYOUT]);
    }

    /**
     * @test
     */
    public function shouldNotOverwriteAlreadySetExtraDetails()
    {
        $order = new Payout();
        $order->setCurrencyCode('USD');
        $order->setTotalAmount(123);
        $order->setDescription('the description');
        $order->setDetails(array(
            'foo' => 'fooVal',
        ));

        $action = new ConvertPayoutAction();

        $action->execute($convert = new Convert($order, 'array'));

        $details = $convert->getResult();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertEquals('fooVal', $details['foo']);
    }
}
