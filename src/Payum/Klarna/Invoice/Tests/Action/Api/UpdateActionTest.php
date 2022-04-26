<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Tests\GenericApiAwareActionTest;
use Payum\Klarna\Invoice\Action\Api\UpdateAction;
use Payum\Klarna\Invoice\Config;
use Payum\Klarna\Invoice\Request\Api\Update;
use PHPUnit\Framework\TestCase;
use PhpXmlRpc\Client;

class UpdateActionTest extends GenericApiAwareActionTest
{
    protected function getActionClass(): string
    {
        return UpdateAction::class;
    }

    protected function getApiClass()
    {
        return new Config();
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\UpdateAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function shouldImplementsGatewayAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\UpdateAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\GatewayAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldAllowSetGateway()
    {
        $this->assertInstanceOf(GatewayAwareInterface::class, new UpdateAction($this->createKlarnaMock()));
    }

    /**
     * @test
     */
    public function throwApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = new UpdateAction($this->createKlarnaMock());

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportUpdateWithArrayAsModel()
    {
        $action = new UpdateAction();

        $this->assertTrue($action->supports(new Update(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotUpdate()
    {
        $action = new UpdateAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportUpdateWithNotArrayAccessModel()
    {
        $action = new UpdateAction();

        $this->assertFalse($action->supports(new Update(new \stdClass())));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new UpdateAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldCallKlarnaUpdate()
    {
        $details = array(
            'rno' => 'theRno',
        );

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails'))
        ;

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('update')
            ->with($details['rno'])
            ->will($this->returnValue(true))
        ;

        $action = new UpdateAction($klarnaMock);
        $action->setApi(new Config());
        $action->setGateway($gatewayMock);

        $action->execute($request = new Update($details));

        $model = $request->getModel();
        $this->assertEquals('theRno', $model['rno']);
        $this->assertTrue($model['updated']);
    }

    /**
     * @test
     */
    public function shouldCatchKlarnaExceptionAndSetErrorInfoToDetails()
    {
        $details = array(
            'rno' => 'theRno',
        );

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails'))
        ;

        $klarnaMock = $this->createKlarnaMock();
        $klarnaMock
            ->expects($this->once())
            ->method('update')
            ->with($details['rno'])
            ->will($this->throwException(new \KlarnaException('theMessage', 123)))
        ;

        $action = new UpdateAction($klarnaMock);
        $action->setApi(new Config());
        $action->setGateway($gatewayMock);

        $action->execute($request = new Update($details));

        $model = $request->getModel();
        $this->assertEquals(123, $model['error_code']);
        $this->assertEquals('theMessage', $model['error_message']);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Klarna
     */
    protected function createKlarnaMock()
    {
        $klarnaMock =  $this->createMock('Klarna', array('config', 'update'));

        $rp = new \ReflectionProperty($klarnaMock, 'xmlrpc');
        $rp->setAccessible(true);
        $rp->setValue($klarnaMock, $this->createMock(class_exists('xmlrpc_client') ? 'xmlrpc_client' : Client::class));
        $rp->setAccessible(false);

        return $klarnaMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
