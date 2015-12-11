<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\Tests\SkipOnPhp7Trait;
use Payum\Klarna\Invoice\Action\Api\ReturnAmountAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\ReturnAmount;

class ReturnAmountActionTest extends \PHPUnit_Framework_TestCase
{
    use SkipOnPhp7Trait;

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\ReturnAmountAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new ReturnAmountAction();
    }

    /**
     * @test
     */
    public function couldBeConstructedWithKlarnaAsArgument()
    {
        new ReturnAmountAction($this->createKlarnaMock());
    }

    /**
     * @test
     */
    public function shouldAllowSetConfigAsApi()
    {
        $action = new ReturnAmountAction($this->createKlarnaMock());

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
        $action = new ReturnAmountAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportReserveAmountWithArrayAsModel()
    {
        $action = new ReturnAmountAction();

        $this->assertTrue($action->supports(new ReturnAmount(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotReserveAmount()
    {
        $action = new ReturnAmountAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportReturnAmountWithNotArrayAccessModel()
    {
        $action = new ReturnAmountAction();

        $this->assertFalse($action->supports(new ReturnAmount(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new ReturnAmountAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldCallKlarnaReturnAmount()
    {
        $details = array(
            'invoice_number' => 'invoice number',
            'amount' => 100,
            'vat' => 50,
            'flags' => 123,
            'description' => 'description',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('returnAmount')
            ->with(
                $details['invoice_number'],
                $details['amount'],
                $details['vat'],
                $details['flags'],
                $details['description']
            )
        ;

        $action = new ReturnAmountAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute(new ReturnAmount($details));
    }

    /**
     * @test
     */
    public function shouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
    {
        $details = array(
            'invoice_number' => 'invoice number',
            'amount' => 100,
            'vat' => 50,
            'flags' => 123,
            'description' => 'description',
        );

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('returnAmount')
            ->with(
                $details['invoice_number'],
                $details['amount'],
                $details['vat'],
                $details['flags'],
                $details['description']
            )
            ->will($this->throwException(new \KlarnaException('theMessage', 123)))
        ;

        $action = new ReturnAmountAction($klarnaMock);
        $action->setApi(new Config());

        $action->execute($reserve = new ReturnAmount($details));

        $postDetails = $reserve->getModel();
        $this->assertEquals(123, $postDetails['error_code']);
        $this->assertEquals('theMessage', $postDetails['error_message']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna
     */
    protected function createKlarnaMock()
    {
        $klarnaMock =  $this->getMock('Klarna', array('config', 'returnAmount'));

        $rp = new \ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->getMock('xmlrpc_client', array(), array(), '', false));
        $rp->setAccessible(false);

        return $klarnaMock;
    }
}
