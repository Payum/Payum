<?php

namespace Payum\Core\Tests\Extension;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Extension\GenericTokenFactoryExtension;
use Payum\Core\GatewayInterface;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class GenericTokenFactoryExtensionTest extends TestCase
{
    public function testShouldImplementExtensionInterface(): void
    {
        $rc = new ReflectionClass(GenericTokenFactoryExtension::class);

        $this->assertTrue($rc->implementsInterface(ExtensionInterface::class));
    }

    public function testShouldSetTokenFactoryToActionImplementsGenericTokenFactoryAwareInterface(): void
    {
        $tokenFactory = $this->createGenericTokenFactoryMock();

        $extension = new GenericTokenFactoryExtension($tokenFactory);

        $action = new ActionGenericTokenFactoryAware();

        $context = new Context($this->createGatewayMock(), new stdClass(), []);
        $context->setAction($action);

        $extension->onExecute($context);

        $this->assertSame($tokenFactory, $action->tokenFactory);
    }

    public function testShouldUnsetGenericTokenFactoryOnPostExecute(): void
    {
        $tokenFactory = $this->createGenericTokenFactoryMock();

        $extension = new GenericTokenFactoryExtension($tokenFactory);

        $action = new ActionGenericTokenFactoryAware();
        $action->tokenFactory = $tokenFactory;

        $context = new Context($this->createGatewayMock(), new stdClass(), []);
        $context->setAction($action);

        $extension->onPostExecute($context);

        $this->assertNull($action->tokenFactory);
    }

    public function testShouldDoNothingIfActionNotImplementsGenericTokenFactoryAwareInterfaceOnPostExecute(): void
    {
        $this->expectNotToPerformAssertions();

        $tokenFactory = $this->createGenericTokenFactoryMock();

        $extension = new GenericTokenFactoryExtension($tokenFactory);

        $action = $this->createActionMock();

        $context = new Context($this->createGatewayMock(), new stdClass(), []);
        $context->setAction($action);

        $extension->onPostExecute($context);
    }

    /**
     * @return MockObject|ActionInterface
     */
    protected function createActionMock()
    {
        return $this->createMock(ActionInterface::class);
    }

    /**
     * @return MockObject|GenericTokenFactoryInterface
     */
    protected function createGenericTokenFactoryMock()
    {
        return $this->createMock(GenericTokenFactoryInterface::class);
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}

class ActionGenericTokenFactoryAware implements ActionInterface, GenericTokenFactoryAwareInterface
{
    public $tokenFactory;

    public function execute($request): void
    {
    }

    public function supports($request): bool
    {
        return true;
    }

    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null): void
    {
        $this->tokenFactory = $genericTokenFactory;
    }
}
