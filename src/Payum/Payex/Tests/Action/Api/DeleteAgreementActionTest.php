<?php
namespace Payum\Payex\Tests\Action\Api;

use Payum\Payex\Action\Api\DeleteAgreementAction;
use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Request\Api\DeleteAgreement;

class DeleteAgreementActionTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\DeleteAgreementAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\DeleteAgreementAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new DeleteAgreementAction();
    }

    /**
     * @test
     */
    public function shouldAllowSetAgreementApiAsApi()
    {
        $agreementApi = $this->getMock('Payum\Payex\Api\AgreementApi', array(), array(), '', false);

        $action = new DeleteAgreementAction();

        $action->setApi($agreementApi);

        $this->assertAttributeSame($agreementApi, 'api', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     * @expectedExceptionMessage Expected api must be instance of AgreementApi.
     */
    public function throwOnTryingSetNotAgreementApiAsApi()
    {
        $action = new DeleteAgreementAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportDeleteAgreementRequestWithArrayAccessAsModel()
    {
        $action = new DeleteAgreementAction();

        $this->assertTrue($action->supports(new DeleteAgreement($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotDeleteAgreementRequest()
    {
        $action = new DeleteAgreementAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportDeleteAgreementRequestWithNotArrayAccessModel()
    {
        $action = new DeleteAgreementAction();

        $this->assertFalse($action->supports(new DeleteAgreement(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new DeleteAgreementAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @dataProvider provideRequiredNotEmptyFields
     *
     * @expectedException \Payum\Core\Exception\LogicException
     */
    public function throwIfTryInitializeWithRequiredFieldEmpty($requiredField)
    {
        $fields = $this->requiredNotEmptyFields;

        $fields[$requiredField] = '';

        $action = new DeleteAgreementAction();

        $action->execute(new DeleteAgreement($fields));
    }

    /**
     * @test
     */
    public function shouldCheckAgreementAndSetAgreementStatusAsResult()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('delete')
            ->with($this->requiredNotEmptyFields)
            ->will($this->returnValue(array(
                'errorCode' => AgreementApi::ERRORCODE_OK,
            )));

        $action = new DeleteAgreementAction();
        $action->setApi($apiMock);

        $request = new DeleteAgreement($this->requiredNotEmptyFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertEquals(AgreementApi::ERRORCODE_OK, $model['errorCode']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Payex\Api\AgreementApi
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Payex\Api\AgreementApi', array(), array(), '', false);
    }
}
