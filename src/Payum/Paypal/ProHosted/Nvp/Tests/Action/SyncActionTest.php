<?php

namespace Payum\Paypal\ProHosted\Nvp\Tests\Action;

use ArrayObject;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Sync;
use Payum\Paypal\ProHosted\Nvp\Action\SyncAction;
use Payum\Paypal\ProHosted\Nvp\Request\Api\GetTransactionDetails;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class SyncActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(SyncAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface(): void
    {
        $rc = new ReflectionClass(SyncAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportSyncAndArrayAsModelWhichHasPaymentRequestAmountSet(): void
    {
        $action = new SyncAction();

        $paymentDetails = [
            'AMT' => 12,
        ];

        $request = new Sync($paymentDetails);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldSupportSyncAndArrayAsModelWhichHasPaymentRequestAmountSetToZero(): void
    {
        $action = new SyncAction();

        $paymentDetails = [
            'AMT' => 0,
        ];

        $request = new Sync($paymentDetails);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotSync(): void
    {
        $action = new SyncAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new SyncAction();

        $action->execute(new stdClass());
    }

    public function testShouldRequestGetTransactionDetailsAndUpdateModelIfTransactionIdSetInModel(): void
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(GetTransactionDetails::class))
            ->willReturnCallback(function (GetTransactionDetails $request): void {
                $model = $request->getModel();
                $model['foo'] = 'fooVal';
                $model['AMT'] = 33;
            })
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $details = new ArrayObject([
            'AMT' => 11,
            'txn_id' => 'aTxn_id',
        ]);

        $action->execute($sync = new Sync($details));

        $this->assertArrayHasKey('foo', (array) $details);
        $this->assertSame('fooVal', $details['foo']);

        $this->assertArrayHasKey('AMT', (array) $details);
        $this->assertSame(33, $details['AMT']);
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
