<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoVoidAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoVoid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class DoVoidActionTest extends TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new ReflectionClass(DoVoidAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new ReflectionClass(DoVoidAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new ReflectionClass(DoVoidAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldUseApiAwareTrait()
    {
        $rc = new ReflectionClass(DoVoidAction::class);

        $this->assertContains(ApiAwareTrait::class, $rc->getTraitNames());
    }

    public function testShouldUseGatewayAwareTrait()
    {
        $rc = new ReflectionClass(DoVoidAction::class);

        $this->assertContains(GatewayAwareTrait::class, $rc->getTraitNames());
    }

    public function testShouldSupportDoVoidRequestAndArrayAccessAsModel()
    {
        $action = new DoVoidAction();

        $this->assertTrue(
            $action->supports(new DoVoid($this->createMock(ArrayAccess::class)))
        );
    }

    public function testShouldNotSupportAnythingNotDoVoidRequest()
    {
        $action = new DoVoidAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new DoVoidAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfAuthorizationIdNotSetInModel()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('AUTHORIZATIONID must be set. Has user not authorized this transaction?');
        $action = new DoVoidAction();

        $request = new DoVoid([]);

        $action->execute($request);
    }

    public function testShouldCallApiDoVoidMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('DoVoid')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('AUTHORIZATIONID', $fields);
                $testCase->assertSame('theOriginalTransactionId', $fields['AUTHORIZATIONID']);

                return [];
            })
        ;

        $action = new DoVoidAction();
        $action->setApi($apiMock);

        $request = new DoVoid([
            'AUTHORIZATIONID' => 'theOriginalTransactionId',
        ]);

        $action->execute($request);
    }

    public function testShouldCallApiDoVoidMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('DoVoid')
            ->willReturnCallback(function () {
                return [
                    'AUTHORIZATIONID' => 'theTransactionId',
                    'MSGSUBID' => 'aMessageId',
                ];
            })
        ;

        $action = new DoVoidAction();
        $action->setApi($apiMock);

        $request = new DoVoid([
            'AUTHORIZATIONID' => 'theTransactionId',
        ]);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('AUTHORIZATIONID', $model);
        $this->assertSame('theTransactionId', $model['AUTHORIZATIONID']);

        $this->assertArrayHasKey('MSGSUBID', $model);
        $this->assertSame('aMessageId', $model['MSGSUBID']);
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class, [], [], '', false);
    }
}
