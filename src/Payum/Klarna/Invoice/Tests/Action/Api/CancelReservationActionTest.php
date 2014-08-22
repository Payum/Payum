<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Klarna\Invoice\Action\Api\CancelReservationAction;
use Payum\Klarna\Invoice\Request\Api\CancelReservation;

class CancelReservationActionTest extends \PHPUnit_Framework_TestCase
{
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
        new CancelReservationAction;
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
    public function shouldSupportCancelReservationWithArrayAsModel()
    {
        $action = new CancelReservationAction;

        $this->assertTrue($action->supports(new CancelReservation(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCancelReservation()
    {
        $action = new CancelReservationAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCancelReservationWithNotArrayAccessModel()
    {
        $action = new CancelReservationAction;

        $this->assertFalse($action->supports(new CancelReservation(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new CancelReservationAction;

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The rno fields is required.
     */
    public function throwIfRnoNotSet()
    {
        $action = new CancelReservationAction;

        $action->execute(new CancelReservation(array()));
    }

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

        $action->execute($activate = new CancelReservation($details));

        $canceledDetails = $activate->getModel();

        $this->assertTrue($canceledDetails['canceled']);
    }

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
            ->will($this->returnThrow(new \KlarnaException('theMessage', 'theCode')))
        ;

        $action = new CancelReservationAction($klarnaMock);

        $action->execute($cancel = new CancelReservation($details));

        $activatedDetails = $cancel->getModel();
        $this->assertEquals('theCode', $activatedDetails['error_code']);
        $this->assertEquals('theMessage', $activatedDetails['error_message']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna
     */
    protected function createKlarnaMock()
    {
        return $this->getMock('Klarna');
    }
}