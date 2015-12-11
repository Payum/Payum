<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\Tests\SkipOnPhp7Trait;
use Payum\Klarna\Invoice\Action\Api\CancelReservationAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\CancelReservation;

class CancelReservationActionTest extends \PHPUnit_Framework_TestCase
{
    use SkipOnPhp7Trait;

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\CancelReservationAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CancelReservationAction();
    }

    /**
     * @test
     */
    public function couldBeConstructedWithKlarnaAsArgument()
    {
        new CancelReservationAction($this->createKlarnaMock());
    }

    /**
     * @test
     */
    public function shouldAllowSetConfigAsApi()
    {
        $action = new CancelReservationAction($this->createKlarnaMock());

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
        $action = new CancelReservationAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportCancelReservationWithArrayAsModel()
    {
        $action = new CancelReservationAction();

        $this->assertTrue($action->supports(new CancelReservation(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCancelReservation()
    {
        $action = new CancelReservationAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCancelReservationWithNotArrayAccessModel()
    {
        $action = new CancelReservationAction();

        $this->assertFalse($action->supports(new CancelReservation(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new CancelReservationAction();

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
        $action = new CancelReservationAction();

        $action->execute(new CancelReservation(array()));
    }

    /**
     * @test
     */
    public function shouldCallKlarnaCancelReservationMethod()
    {
        $details = array(
            'rno' => 'theRno',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('cancelReservation')
            ->with($details['rno'])
            ->will($this->returnValue(true))
        ;

        $action = new CancelReservationAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($activate = new CancelReservation($details));

        $canceledDetails = $activate->getModel();

        $this->assertTrue($canceledDetails['canceled']);
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
            ->method('cancelReservation')
            ->with($details['rno'])
            ->will($this->throwException(new \KlarnaException('theMessage', 123)))
        ;

        $action = new CancelReservationAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($cancel = new CancelReservation($details));

        $activatedDetails = $cancel->getModel();
        $this->assertEquals(123, $activatedDetails['error_code']);
        $this->assertEquals('theMessage', $activatedDetails['error_message']);
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
