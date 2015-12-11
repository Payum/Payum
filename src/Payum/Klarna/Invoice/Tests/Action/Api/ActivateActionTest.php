<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\Tests\SkipOnPhp7Trait;
use Payum\Klarna\Invoice\Action\Api\ActivateAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\Activate;

class ActivateActionTest extends \PHPUnit_Framework_TestCase
{
    use SkipOnPhp7Trait;

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\ActivateAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new ActivateAction();
    }

    /**
     * @test
     */
    public function couldBeConstructedWithKlarnaAsArgument()
    {
        new ActivateAction($this->createKlarnaMock());
    }

    /**
     * @test
     */
    public function shouldAllowSetConfigAsApi()
    {
        $action = new ActivateAction($this->createKlarnaMock());

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
        $action = new ActivateAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportActivateWithArrayAsModel()
    {
        $action = new ActivateAction();

        $this->assertTrue($action->supports(new Activate(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotActivate()
    {
        $action = new ActivateAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportActivateWithNotArrayAccessModel()
    {
        $action = new ActivateAction();

        $this->assertFalse($action->supports(new Activate(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new ActivateAction();

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
        $action = new ActivateAction();

        $action->execute(new Activate(array()));
    }

    /**
     * @test
     */
    public function shouldCallKlarnaActivate()
    {
        $details = array(
            'rno' => 'theRno',
            'osr' => 'theOsr',
            'activation_flags' => 'theFlags',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('activate')
            ->with(
                $details['rno'],
                $details['osr'],
                $details['activation_flags']
            )
            ->will($this->returnValue(array('theRisk', 'theInvNumber')))
        ;

        $action = new ActivateAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($activate = new Activate($details));

        $activatedDetails = $activate->getModel();
        $this->assertEquals('theRisk', $activatedDetails['risk_status']);
        $this->assertEquals('theInvNumber', $activatedDetails['invoice_number']);
    }

    /**
     * @test
     */
    public function shouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
    {
        $details = array(
            'rno' => 'theRno',
            'osr' => 'theOsr',
            'activation_flags' => 'theFlags',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('activate')
            ->with(
                $details['rno'],
                $details['osr'],
                $details['activation_flags']
            )
            ->will($this->throwException(new \KlarnaException('theMessage', 123)))
        ;

        $action = new ActivateAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($activate = new Activate($details));

        $activatedDetails = $activate->getModel();

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
