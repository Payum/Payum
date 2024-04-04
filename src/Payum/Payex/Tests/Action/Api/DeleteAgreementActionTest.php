<?php
namespace Payum\Payex\Tests\Action\Api;

use Payum\Payex\Action\Api\DeleteAgreementAction;
use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Request\Api\DeleteAgreement;

class DeleteAgreementActionTest extends \PHPUnit\Framework\TestCase
{
    protected $requiredNotEmptyFields = array(
        'agreementRef' => 'anAgreementRef',
    );

    public function provideRequiredNotEmptyFields()
    {
        $fields = array();

        foreach ($this->requiredNotEmptyFields as $name => $value) {
            $fields[] = array($name);
        }

        return $fields;
    }

    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\DeleteAgreementAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\DeleteAgreementAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    public function testThrowOnTryingSetNotAgreementApiAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\AgreementApi');
        $action = new DeleteAgreementAction();

        $action->setApi(new \stdClass());
    }

    public function testShouldSupportDeleteAgreementRequestWithArrayAccessAsModel()
    {
        $action = new DeleteAgreementAction();

        $this->assertTrue($action->supports(new DeleteAgreement($this->createMock('ArrayAccess'))));
    }

    public function testShouldNotSupportAnythingNotDeleteAgreementRequest()
    {
        $action = new DeleteAgreementAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportDeleteAgreementRequestWithNotArrayAccessModel()
    {
        $action = new DeleteAgreementAction();

        $this->assertFalse($action->supports(new DeleteAgreement(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new DeleteAgreementAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @dataProvider provideRequiredNotEmptyFields
     */
    public function testThrowIfTryInitializeWithRequiredFieldEmpty($requiredField)
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
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
            ->willReturn(array(
                'errorCode' => AgreementApi::ERRORCODE_OK,
            ));

        $action = new DeleteAgreementAction();
        $action->setApi($apiMock);

        $request = new DeleteAgreement($this->requiredNotEmptyFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame(AgreementApi::ERRORCODE_OK, $model['errorCode']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Payex\Api\AgreementApi
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Payex\Api\AgreementApi', array(), array(), '', false);
    }
}
