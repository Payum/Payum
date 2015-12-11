<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\Tests\SkipOnPhp7Trait;
use Payum\Klarna\Invoice\Action\Api\CheckOrderStatusAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\CheckOrderStatus;

class CheckOrderStatusActionTest extends \PHPUnit_Framework_TestCase
{
    use SkipOnPhp7Trait;

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\CheckOrderStatusAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CheckOrderStatusAction();
    }

    /**
     * @test
     */
    public function couldBeConstructedWithKlarnaAsArgument()
    {
        new CheckOrderStatusAction($this->createKlarnaMock());
    }

    /**
     * @test
     */
    public function shouldAllowSetConfigAsApi()
    {
        $action = new CheckOrderStatusAction($this->createKlarnaMock());

        $action->setApi($config = new Config());

        $this->assertAttributeSame($config, 'config', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     * @expectedExceptionMessage Instance of Config is expected to be passed as api.
     */
    public function throwApiNotSupportedIfNotConfigGivenAsApi()
    {
        $action = new CheckOrderStatusAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportCheckOrderStatusWithArrayAsModel()
    {
        $action = new CheckOrderStatusAction();

        $this->assertTrue($action->supports(new CheckOrderStatus(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCheckOrderStatus()
    {
        $action = new CheckOrderStatusAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCheckOrderStatusWithNotArrayAccessModel()
    {
        $action = new CheckOrderStatusAction();

        $this->assertFalse($action->supports(new CheckOrderStatus(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new CheckOrderStatusAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The rno fields are required.
     */
    public function throwIfRnoNotSet()
    {
        $action = new CheckOrderStatusAction();

        $action->execute(new CheckOrderStatus(array()));
    }

    /**
     * @test
     */
    public function shouldCallKlarnaCheckOrderStatusMethod()
    {
        $details = array(
            'rno' => 'theRno',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('checkOrderStatus')
            ->with($details['rno'])
            ->will($this->returnValue('theStatus'))
        ;

        $action = new CheckOrderStatusAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($check = new CheckOrderStatus($details));

        $activatedDetails = $check->getModel();

        $this->assertEquals('theStatus', $activatedDetails['status']);
    }

    /**
     * @test
     */
    public function shouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
    {
        $details = array(
            'rno' => 'theRno',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('checkOrderStatus')
            ->with($details['rno'])
            ->will($this->throwException(new \KlarnaException('theMessage', 123)))
        ;

        $action = new CheckOrderStatusAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($activate = new CheckOrderStatus($details));

        $activatedDetails = $activate->getModel();
        $this->assertEquals(123, $activatedDetails['error_code']);
        $this->assertEquals('theMessage', $activatedDetails['error_message']);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfAlreadyActivated()
    {
        $details = array(
            'rno' => 'theRno',
            'invoice_number' => 'anInvNumber',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->never())
            ->method('checkOrderStatus')
        ;

        $action = new CheckOrderStatusAction($klarnaMock);

        $action->execute($activate = new CheckOrderStatus($details));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna
     */
    protected function createKlarnaMock()
    {
        $klarnaMock =  $this->getMock('Klarna', array('config', 'activate', 'cancelReservation', 'checkOrderStatus'));

        $rp = new \ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->getMock('xmlrpc_client', array(), array(), '', false));
        $rp->setAccessible(false);

        return $klarnaMock;
    }
}
