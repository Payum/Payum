<?php
namespace Payum\Payex\Tests\Action\Api;

use Payum\Payex\Action\Api\CheckAgreementAction;
use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Request\Api\CheckAgreement;

class CheckAgreementActionTest extends \PHPUnit_Framework_TestCase
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
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\CheckAgreementAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\CheckAgreementAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CheckAgreementAction();
    }

    /**
     * @test
     */
    public function shouldAllowSetAgreementApiAsApi()
    {
        $agreementApi = $this->getMock('Payum\Payex\Api\AgreementApi', array(), array(), '', false);

        $action = new CheckAgreementAction();

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
        $action = new CheckAgreementAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportCheckAgreementRequestWithArrayAccessAsModel()
    {
        $action = new CheckAgreementAction();

        $this->assertTrue($action->supports(new CheckAgreement($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCheckAgreementRequest()
    {
        $action = new CheckAgreementAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCheckAgreementRequestWithNotArrayAccessModel()
    {
        $action = new CheckAgreementAction();

        $this->assertFalse($action->supports(new CheckAgreement(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CheckAgreementAction($this->createApiMock());

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

        $action = new CheckAgreementAction();

        $action->execute(new CheckAgreement($fields));
    }

    /**
     * @test
     */
    public function shouldCheckAgreementAndSetAgreementStatusAsResult()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('check')
            ->with($this->requiredNotEmptyFields)
            ->will($this->returnValue(array(
                'agreementStatus' => AgreementApi::AGREEMENTSTATUS_VERIFIED,
            )));

        $action = new CheckAgreementAction();
        $action->setApi($apiMock);

        $request = new CheckAgreement($this->requiredNotEmptyFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertEquals(AgreementApi::AGREEMENTSTATUS_VERIFIED, $model['agreementStatus']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Payex\Api\AgreementApi
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Payex\Api\AgreementApi', array(), array(), '', false);
    }
}
