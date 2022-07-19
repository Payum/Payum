<?php

namespace Payum\Payex\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Payex\Action\Api\CreateAgreementAction;
use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Request\Api\CreateAgreement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CreateAgreementActionTest extends TestCase
{
    /**
     * @var array{merchantRef: string, description: string, purchaseOperation: string, maxAmount: int, startDate: string, stopDate: string}
     */
    protected array $requiredFields = [
        'merchantRef' => 'aMerchRef',
        'description' => 'aDesc',
        'purchaseOperation' => AgreementApi::PURCHASEOPERATION_SALE,
        'maxAmount' => 100000,
        'startDate' => '',
        'stopDate' => '',
    ];

    /**
     * @var array{merchantRef: string, description: string, maxAmount: int}
     */
    protected array $requiredNotEmptyFields = [
        'merchantRef' => 'aMerchRef',
        'description' => 'aDesc',
        'maxAmount' => 100000,
    ];

    /**
     * @return array<int, mixed[]>
     */
    public function provideRequiredFields(): array
    {
        $fields = [];

        foreach ($this->requiredFields as $name => $value) {
            $fields[] = [$name];
        }

        return $fields;
    }

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
        $rc = new ReflectionClass(CreateAgreementAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface(): void
    {
        $rc = new ReflectionClass(CreateAgreementAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowOnTryingSetNotAgreementApiAsApi(): void
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\AgreementApi');
        $action = new CreateAgreementAction();

        $action->setApi(new stdClass());
    }

    public function testShouldSupportCreateAgreementRequestWithArrayAccessAsModel(): void
    {
        $action = new CreateAgreementAction();

        $this->assertTrue($action->supports(new CreateAgreement($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotCreateAgreementRequest(): void
    {
        $action = new CreateAgreementAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportCreateAgreementRequestWithNotArrayAccessModel(): void
    {
        $action = new CreateAgreementAction();

        $this->assertFalse($action->supports(new CreateAgreement(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute(): void
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CreateAgreementAction($this->createApiMock());

        $action->execute(new stdClass());
    }

    /**
     * @dataProvider provideRequiredFields
     */
    public function testThrowIfTryInitializeWithRequiredFieldNotPresent($requiredField): void
    {
        $this->expectException(LogicException::class);
        unset($this->requiredFields[$requiredField]);

        $action = new CreateAgreementAction();

        $action->execute(new CreateAgreement($this->requiredFields));
    }

    /**
     * @dataProvider provideRequiredNotEmptyFields
     */
    public function testThrowIfTryInitializeWithRequiredFieldEmpty($requiredField): void
    {
        $this->expectException(LogicException::class);
        $fields = $this->requiredNotEmptyFields;

        $fields[$requiredField] = '';

        $action = new CreateAgreementAction();

        $action->execute(new CreateAgreement($fields));
    }

    public function testShouldCreateAgreementPayment(): void
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('create')
            ->with($this->requiredFields)
            ->willReturn([
                'agreementRef' => 'theRef',
            ]);

        $action = new CreateAgreementAction();
        $action->setApi($apiMock);

        $request = new CreateAgreement($this->requiredFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame('theRef', $model['agreementRef']);
    }

    public function testThrowIfTryCreateAlreadyCreatedAgreement(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The agreement has already been created.');
        $action = new CreateAgreementAction();

        $request = new CreateAgreement([
            'agreementRef' => 'aRef',
        ]);

        $action->execute($request);
    }

    /**
     * @return MockObject|AgreementApi
     */
    protected function createApiMock()
    {
        return $this->createMock(AgreementApi::class);
    }
}
