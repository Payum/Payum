<?php
namespace Payum\Core\Tests\Extension;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\GenericTokenFactoryExtension;
use Payum\Core\GatewayInterface;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;

class GenericTokenFactoryExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Extension\GenericTokenFactoryExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Extension\ExtensionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithGenericTokenFactoryAsArgument()
    {
        new GenericTokenFactoryExtension($this->createGenericTokenFactoryMock());
    }

    /**
     * @test
     */
    public function shouldSetTokenFactoryToActionImplementsGenericTokenFactoryAwareInterface()
    {
        $tokenFactory = $this->createGenericTokenFactoryMock();

        $extension = new GenericTokenFactoryExtension($tokenFactory);

        $action = new ActionGenericTokenFactoryAware();

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());
        $context->setAction($action);

        $extension->onExecute($context);

        $this->assertSame($tokenFactory, $action->tokenFactory);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfActionNotImplementsGenericTokenFactoryAwareInterface()
    {
        $tokenFactory = $this->createGenericTokenFactoryMock();

        $extension = new GenericTokenFactoryExtension($tokenFactory);

        $action = $this->createActionMock();

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());
        $context->setAction($action);

        $extension->onExecute($context);
    }

    /**
     * @test
     */
    public function shouldDoNothingOnPreExecute()
    {
        $tokenFactory = $this->createGenericTokenFactoryMock();

        $extension = new GenericTokenFactoryExtension($tokenFactory);

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());

        $extension->onPreExecute($context);
    }

    /**
     * @test
     */
    public function shouldUnsetGenericTokenFactoryOnPostExecute()
    {
        $tokenFactory = $this->createGenericTokenFactoryMock();

        $extension = new GenericTokenFactoryExtension($tokenFactory);

        $action = new ActionGenericTokenFactoryAware();
        $action->tokenFactory = $tokenFactory;

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());
        $context->setAction($action);

        $extension->onPostExecute($context);

        $this->assertNull($action->tokenFactory);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfActionNotImplementsGenericTokenFactoryAwareInterfaceOnPostExecute()
    {
        $tokenFactory = $this->createGenericTokenFactoryMock();

        $extension = new GenericTokenFactoryExtension($tokenFactory);

        $action = $this->createActionMock();

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());
        $context->setAction($action);

        $extension->onPostExecute($context);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ActionInterface
     */
    protected function createActionMock()
    {
        return $this->getMock('Payum\Core\Action\ActionInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GenericTokenFactoryInterface
     */
    protected function createGenericTokenFactoryMock()
    {
        return $this->getMock('Payum\Core\Security\GenericTokenFactoryInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock('Payum\Core\GatewayInterface');
    }
}

class ActionGenericTokenFactoryAware implements ActionInterface, GenericTokenFactoryAwareInterface
{
    public $tokenFactory;

    public function execute($request)
    {
    }

    public function supports($request)
    {
    }

    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null)
    {
        $this->tokenFactory  = $genericTokenFactory;
    }
}
