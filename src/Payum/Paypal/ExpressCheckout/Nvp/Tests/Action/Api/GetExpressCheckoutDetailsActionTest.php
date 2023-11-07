<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetExpressCheckoutDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetExpressCheckoutDetails;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class GetExpressCheckoutDetailsActionTest extends TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new ReflectionClass(GetExpressCheckoutDetailsAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface()
    {
        $rc = new ReflectionClass(GetExpressCheckoutDetailsAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportGetExpressCheckoutDetailsRequestAndArrayAccessAsModel()
    {
        $action = new GetExpressCheckoutDetailsAction();

        $this->assertTrue(
            $action->supports(new GetExpressCheckoutDetails($this->createMock(ArrayAccess::class)))
        );
    }

    public function testShouldNotSupportAnythingNotGetExpressCheckoutDetailsRequest()
    {
        $action = new GetExpressCheckoutDetailsAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new GetExpressCheckoutDetailsAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfTokenNotSetInModel()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('TOKEN must be set. Have you run SetExpressCheckoutAction?');
        $action = new GetExpressCheckoutDetailsAction();

        $request = new GetExpressCheckoutDetails([]);

        $action->execute($request);
    }

    public function testShouldCallApiGetExpressCheckoutDetailsMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getExpressCheckoutDetails')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TOKEN', $fields);
                $testCase->assertSame('theToken', $fields['TOKEN']);

                return [];
            })
        ;

        $action = new GetExpressCheckoutDetailsAction();
        $action->setApi($apiMock);

        $request = new GetExpressCheckoutDetails([
            'TOKEN' => 'theToken',
        ]);

        $action->execute($request);
    }

    public function testShouldCallApiGetExpressCheckoutDetailsMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getExpressCheckoutDetails')
            ->willReturnCallback(function () {
                return [
                    'FIRSTNAME' => 'theFirstname',
                    'EMAIL' => 'the@example.com',
                ];
            })
        ;

        $action = new GetExpressCheckoutDetailsAction();
        $action->setApi($apiMock);

        $request = new GetExpressCheckoutDetails([
            'TOKEN' => 'aToken',
        ]);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('FIRSTNAME', $model);
        $this->assertSame('theFirstname', $model['FIRSTNAME']);

        $this->assertArrayHasKey('EMAIL', $model);
        $this->assertSame('the@example.com', $model['EMAIL']);
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }
}
