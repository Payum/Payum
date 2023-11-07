<?php

namespace Payum\Payex\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Payex\Action\Api\DeleteAgreementAction;
use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Request\Api\DeleteAgreement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class DeleteAgreementActionTest extends TestCase
{
    protected $requiredNotEmptyFields = [
        'agreementRef' => 'anAgreementRef',
    ];

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
        $rc = new ReflectionClass(DeleteAgreementAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new ReflectionClass(DeleteAgreementAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testThrowOnTryingSetNotAgreementApiAsApi()
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\AgreementApi');
        $action = new DeleteAgreementAction();

        $action->setApi(new stdClass());
    }

    public function testShouldSupportDeleteAgreementRequestWithArrayAccessAsModel()
    {
        $action = new DeleteAgreementAction();

        $this->assertTrue($action->supports(new DeleteAgreement($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotDeleteAgreementRequest()
    {
        $action = new DeleteAgreementAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportDeleteAgreementRequestWithNotArrayAccessModel()
    {
        $action = new DeleteAgreementAction();

        $this->assertFalse($action->supports(new DeleteAgreement(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new DeleteAgreementAction($this->createApiMock());

        $action->execute(new stdClass());
    }

    #[DataProvider('provideRequiredNotEmptyFields')]
    public function testThrowIfTryInitializeWithRequiredFieldEmpty($requiredField)
    {
        $this->expectException(LogicException::class);
        $fields = $this->requiredNotEmptyFields;

        $fields[$requiredField] = '';

        $action = new DeleteAgreementAction();

        $action->execute(new DeleteAgreement($fields));
    }

    public function testShouldCheckAgreementAndSetAgreementStatusAsResult()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('delete')
            ->with($this->requiredNotEmptyFields)
            ->willReturn([
                'errorCode' => AgreementApi::ERRORCODE_OK,
            ]);

        $action = new DeleteAgreementAction();
        $action->setApi($apiMock);

        $request = new DeleteAgreement($this->requiredNotEmptyFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame(AgreementApi::ERRORCODE_OK, $model['errorCode']);
    }

    /**
     * @return MockObject|AgreementApi
     */
    protected function createApiMock()
    {
        return $this->createMock(AgreementApi::class);
    }
}
