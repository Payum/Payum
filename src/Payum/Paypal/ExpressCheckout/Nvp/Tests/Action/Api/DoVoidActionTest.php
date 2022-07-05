<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoVoidAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoVoid;

class DoVoidActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(\Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoVoidAction::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\Action\ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(\Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoVoidAction::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\ApiAwareInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(\Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoVoidAction::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\GatewayAwareInterface::class));
    }

    public function testShouldUseApiAwareTrait()
    {
        $rc = new \ReflectionClass(\Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoVoidAction::class);

        $this->assertContains(\Payum\Core\ApiAwareTrait::class, $rc->getTraitNames());
    }

    public function testShouldUseGatewayAwareTrait()
    {
        $rc = new \ReflectionClass(\Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoVoidAction::class);

        $this->assertContains(\Payum\Core\GatewayAwareTrait::class, $rc->getTraitNames());
    }

    public function testShouldSupportDoVoidRequestAndArrayAccessAsModel()
    {
        $action = new DoVoidAction();

        $this->assertTrue(
            $action->supports(new DoVoid($this->createMock(\ArrayAccess::class)))
        );
    }

    public function testShouldNotSupportAnythingNotDoVoidRequest()
    {
        $action = new DoVoidAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new DoVoidAction();

        $action->execute(new \stdClass());
    }

    public function testThrowIfAuthorizationIdNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
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
     * @return \PHPUnit\Framework\MockObject\MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock(\Payum\Paypal\ExpressCheckout\Nvp\Api::class, [], [], '', false);
    }
}
