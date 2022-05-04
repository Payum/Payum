<?php
namespace Payum\Core\Tests\Extension;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\GenericTokenFactoryExtension;
use Payum\Core\GatewayInterface;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenericTokenFactoryExtensionTest extends TestCase
{
    public function testShouldImplementExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Extension\GenericTokenFactoryExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Extension\ExtensionInterface'));
    }

    public function testShouldSetTokenFactoryToActionImplementsGenericTokenFactoryAwareInterface()
    {
        $tokenFactory = $this->createGenericTokenFactoryMock();

        $extension = new GenericTokenFactoryExtension($tokenFactory);

        $action = new ActionGenericTokenFactoryAware();

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());
        $context->setAction($action);

        $extension->onExecute($context);

        $this->assertSame($tokenFactory, $action->tokenFactory);
    }

    public function testShouldUnsetGenericTokenFactoryOnPostExecute()
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

    public function testShouldDoNothingIfActionNotImplementsGenericTokenFactoryAwareInterfaceOnPostExecute()
    {
        $this->expectNotToPerformAssertions();

        $tokenFactory = $this->createGenericTokenFactoryMock();

        $extension = new GenericTokenFactoryExtension($tokenFactory);

        $action = $this->createActionMock();

        $context = new Context($this->createGatewayMock(), new \stdClass(), array());
        $context->setAction($action);

        $extension->onPostExecute($context);
    }

    /**
     * @return MockObject|ActionInterface
     */
    protected function createActionMock()
    {
        return $this->createMock('Payum\Core\Action\ActionInterface');
    }

    /**
     * @return MockObject|GenericTokenFactoryInterface
     */
    protected function createGenericTokenFactoryMock()
    {
        return $this->createMock('Payum\Core\Security\GenericTokenFactoryInterface');
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
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
