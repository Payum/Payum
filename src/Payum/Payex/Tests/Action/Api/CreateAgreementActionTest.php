<?php

namespace Payum\Payex\Tests\Action\Api;

use Payum\Payex\Action\Api\CreateAgreementAction;
use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Request\Api\CreateAgreement;

class CreateAgreementActionTest extends \PHPUnit\Framework\TestCase
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
        $rc = new \ReflectionClass(\Payum\Payex\Action\Api\CreateAgreementAction::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Action\ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(\Payum\Payex\Action\Api\CreateAgreementAction::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\ApiAwareInterface::class));
    }

    public function testThrowOnTryingSetNotAgreementApiAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\AgreementApi');
        $action = new CreateAgreementAction();

        $action->setApi(new \stdClass());
    }

    public function testShouldSupportCreateAgreementRequestWithArrayAccessAsModel()
    {
        $action = new CreateAgreementAction();

        $this->assertTrue($action->supports(new CreateAgreement($this->createMock(\ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotCreateAgreementRequest()
    {
        $action = new CreateAgreementAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportCreateAgreementRequestWithNotArrayAccessModel()
    {
        $action = new CreateAgreementAction();

        $this->assertFalse($action->supports(new CreateAgreement(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CreateAgreementAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @dataProvider provideRequiredFields
     */
    public function testThrowIfTryInitializeWithRequiredFieldNotPresent($requiredField)
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        unset($this->requiredFields[$requiredField]);

        $action = new CreateAgreementAction();

        $action->execute(new CreateAgreement($this->requiredFields));
    }

    /**
     * @dataProvider provideRequiredNotEmptyFields
     */
    public function testThrowIfTryInitializeWithRequiredFieldEmpty($requiredField)
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
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
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The agreement has already been created.');
        $action = new CreateAgreementAction();

        $request = new CreateAgreement([
            'agreementRef' => 'aRef',
        ]);

        $action->execute($request);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Payum\Payex\Api\AgreementApi
     */
    protected function createApiMock()
    {
        return $this->createMock(\Payum\Payex\Api\AgreementApi::class, [], [], '', false);
    }
}
