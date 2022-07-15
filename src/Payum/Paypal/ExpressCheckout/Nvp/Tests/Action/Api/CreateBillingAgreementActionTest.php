<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateBillingAgreementAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateBillingAgreement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CreateBillingAgreementActionTest extends TestCase
{
    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(CreateBillingAgreementAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface(): void
    {
        $rc = new ReflectionClass(CreateBillingAgreementAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportCreateBillingAgreementRequestAndArrayAccessAsModel(): void
    {
        $action = new CreateBillingAgreementAction();

        $this->assertTrue($action->supports(new CreateBillingAgreement($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotCreateBillingAgreementRequest(): void
    {
        $action = new CreateBillingAgreementAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CreateBillingAgreementAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfTokenNotSetInModel(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('TOKEN must be set. Have you run SetExpressCheckoutAction?');
        $action = new CreateBillingAgreementAction();

        $action->execute(new CreateBillingAgreement([]));
    }

    public function testShouldCallApiCreateBillingAgreementMethodWithExpectedRequiredArguments(): void
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('createBillingAgreement')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TOKEN', $fields);
                $testCase->assertSame('theToken', $fields['TOKEN']);

                return [];
            })
        ;

        $action = new CreateBillingAgreementAction();
        $action->setApi($apiMock);

        $request = new CreateBillingAgreement([
            'TOKEN' => 'theToken',
        ]);

        $action->execute($request);
    }

    public function testShouldCallApiCreateBillingMethodAndUpdateModelFromResponseOnSuccess(): void
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('createBillingAgreement')
            ->willReturnCallback(fn () => [
                'FIRSTNAME' => 'theFirstname',
                'EMAIL' => 'the@example.com',
            ])
        ;

        $action = new CreateBillingAgreementAction();
        $action->setApi($apiMock);

        $request = new CreateBillingAgreement([
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
