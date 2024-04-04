<?php
namespace Payum\Payex\Tests\Action\Api;

use Payum\Payex\Action\Api\CheckAgreementAction;
use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Request\Api\CheckAgreement;

class CheckAgreementActionTest extends \PHPUnit\Framework\TestCase
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
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\CheckAgreementAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\CheckAgreementAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    public function testThrowOnTryingSetNotAgreementApiAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\AgreementApi');
        $action = new CheckAgreementAction();

        $action->setApi(new \stdClass());
    }

    public function testShouldSupportCheckAgreementRequestWithArrayAccessAsModel()
    {
        $action = new CheckAgreementAction();

        $this->assertTrue($action->supports(new CheckAgreement($this->createMock('ArrayAccess'))));
    }

    public function testShouldNotSupportAnythingNotCheckAgreementRequest()
    {
        $action = new CheckAgreementAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportCheckAgreementRequestWithNotArrayAccessModel()
    {
        $action = new CheckAgreementAction();

        $this->assertFalse($action->supports(new CheckAgreement(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CheckAgreementAction($this->createApiMock());

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

        $action = new CheckAgreementAction();

        $action->execute(new CheckAgreement($fields));
    }

    public function testShouldCheckAgreementAndSetAgreementStatusAsResult()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('check')
            ->with($this->requiredNotEmptyFields)
            ->willReturn(array(
                'agreementStatus' => AgreementApi::AGREEMENTSTATUS_VERIFIED,
            ));

        $action = new CheckAgreementAction();
        $action->setApi($apiMock);

        $request = new CheckAgreement($this->requiredNotEmptyFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame(AgreementApi::AGREEMENTSTATUS_VERIFIED, $model['agreementStatus']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Payex\Api\AgreementApi
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Payex\Api\AgreementApi', array(), array(), '', false);
    }
}
