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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CreateAgreementActionTest extends TestCase
{
    protected $requiredFields = [
        'merchantRef' => 'aMerchRef',
        'description' => 'aDesc',
        'purchaseOperation' => AgreementApi::PURCHASEOPERATION_SALE,
        'maxAmount' => 100000,
        'startDate' => '',
        'stopDate' => '',
    ];

    protected $requiredNotEmptyFields = [
        'merchantRef' => 'aMerchRef',
        'description' => 'aDesc',
        'maxAmount' => 100000,
    ];

    public function provideRequiredFields()
    {
        $fields = [];

        foreach ($this->requiredFields as $name => $value) {
            $fields[] = [$name];
        }

        return $fields;
    }

    public function provideRequiredNotEmptyFields()
    {
        $fields = [];

        foreach ($this->requiredNotEmptyFields as $name => $value) {
            $fields[] = [$name];
        }

        return $fields;
    }

    public function testShouldImplementActionInterface()
    {
        $rc = new ReflectionClass(CreateAgreementAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new ReflectionClass(CreateAgreementAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowOnTryingSetNotAgreementApiAsApi()
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\AgreementApi');
        $action = new CreateAgreementAction();

        $action->setApi(new stdClass());
    }

    public function testShouldSupportCreateAgreementRequestWithArrayAccessAsModel()
    {
        $action = new CreateAgreementAction();

        $this->assertTrue($action->supports(new CreateAgreement($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotCreateAgreementRequest()
    {
        $action = new CreateAgreementAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportCreateAgreementRequestWithNotArrayAccessModel()
    {
        $action = new CreateAgreementAction();

        $this->assertFalse($action->supports(new CreateAgreement(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CreateAgreementAction($this->createApiMock());

        $action->execute(new stdClass());
    }

    #[DataProvider('provideRequiredFields')]
    public function testThrowIfTryInitializeWithRequiredFieldNotPresent($requiredField)
    {
        $this->expectException(LogicException::class);
        unset($this->requiredFields[$requiredField]);

        $action = new CreateAgreementAction();

        $action->execute(new CreateAgreement($this->requiredFields));
    }

    #[DataProvider('provideRequiredNotEmptyFields')]
    public function testThrowIfTryInitializeWithRequiredFieldEmpty($requiredField)
    {
        $this->expectException(LogicException::class);
        $fields = $this->requiredNotEmptyFields;

        $fields[$requiredField] = '';

        $action = new CreateAgreementAction();

        $action->execute(new CreateAgreement($fields));
    }

    public function testShouldCreateAgreementPayment()
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

    public function testThrowIfTryCreateAlreadyCreatedAgreement()
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
