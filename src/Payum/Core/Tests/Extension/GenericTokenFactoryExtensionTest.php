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
    /**
     * @test
     */
    public function shouldImplementExtensionInterface(): void
    {
        $rc = new \ReflectionClass('Payum\Core\Extension\GenericTokenFactoryExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Extension\ExtensionInterface'));
    }

    /**
     * @test
     */
    public function shouldSetTokenFactoryToActionImplementsGenericTokenFactoryAwareInterface(): void
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
    public function shouldUnsetGenericTokenFactoryOnPostExecute(): void
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
    public function shouldDoNothingIfActionNotImplementsGenericTokenFactoryAwareInterfaceOnPostExecute(): void
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
    protected function createActionMock(): MockObject|ActionInterface
    {
        return $this->createMock('Payum\Core\Action\ActionInterface');
    }

    /**
     * @return MockObject|GenericTokenFactoryInterface
     */
    protected function createGenericTokenFactoryMock(): MockObject|GenericTokenFactoryInterface
    {
        return $this->createMock('Payum\Core\Security\GenericTokenFactoryInterface');
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock(): GatewayInterface|MockObject
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
