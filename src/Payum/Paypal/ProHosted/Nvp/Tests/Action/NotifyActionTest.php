<?php

namespace Payum\Paypal\ProHosted\Nvp\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\ProHosted\Nvp\Action\NotifyAction;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;

class NotifyActionTest extends GenericActionTest
{
    /**
     * @var class-string<Notify>
     */
    protected $requestClass = Notify::class;

    /**
     * @var class-string<NotifyAction>
     */
    protected $actionClass = NotifyAction::class;

    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(NotifyAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(NotifyAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSubExecuteSyncWithSameModel(): void
    {
        $expectedModel = [
            'foo' => 'fooVal',
        ];

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
        ;

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Notify($expectedModel));
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
