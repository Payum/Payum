<?php

namespace Payum\Payex\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Payex\Action\Api\CheckAgreementAction;
use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Request\Api\CheckAgreement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CheckAgreementActionTest extends TestCase
{
    /**
     * @var array{agreementRef: string}
     */
    protected array $requiredNotEmptyFields = [
        'agreementRef' => 'anAgreementRef',
    ];

    /**
     * @return array<int, mixed[]>
     */
    public function provideRequiredNotEmptyFields(): array
    {
        $fields = [];

        foreach ($this->requiredNotEmptyFields as $name => $value) {
            $fields[] = [$name];
        }

        return $fields;
    }

    public function testShouldImplementActionInterface(): void
    {
        $rc = new ReflectionClass(CheckAgreementAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface(): void
    {
        $rc = new ReflectionClass(CheckAgreementAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowOnTryingSetNotAgreementApiAsApi(): void
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\AgreementApi');
        $action = new CheckAgreementAction();

        $action->setApi(new stdClass());
    }

    public function testShouldSupportCheckAgreementRequestWithArrayAccessAsModel(): void
    {
        $action = new CheckAgreementAction();

        $this->assertTrue($action->supports(new CheckAgreement($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotCheckAgreementRequest(): void
    {
        $action = new CheckAgreementAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportCheckAgreementRequestWithNotArrayAccessModel(): void
    {
        $action = new CheckAgreementAction();

        $this->assertFalse($action->supports(new CheckAgreement(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CheckAgreementAction($this->createApiMock());

        $action->execute(new stdClass());
    }

    /**
     * @dataProvider provideRequiredNotEmptyFields
     */
    public function testThrowIfTryInitializeWithRequiredFieldEmpty($requiredField): void
    {
        $this->expectException(LogicException::class);
        $fields = $this->requiredNotEmptyFields;

        $fields[$requiredField] = '';

        $action = new CheckAgreementAction();

        $action->execute(new CheckAgreement($fields));
    }

    public function testShouldCheckAgreementAndSetAgreementStatusAsResult(): void
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('check')
            ->with($this->requiredNotEmptyFields)
            ->willReturn([
                'agreementStatus' => AgreementApi::AGREEMENTSTATUS_VERIFIED,
            ]);

        $action = new CheckAgreementAction();
        $action->setApi($apiMock);

        $request = new CheckAgreement($this->requiredNotEmptyFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame(AgreementApi::AGREEMENTSTATUS_VERIFIED, $model['agreementStatus']);
    }

    /**
     * @return MockObject|AgreementApi
     */
    protected function createApiMock()
    {
        return $this->createMock(AgreementApi::class);
    }
}
