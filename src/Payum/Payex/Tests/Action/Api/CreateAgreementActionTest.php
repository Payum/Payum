<?php

namespace Payum\Payex\Tests\Action\Api;

use Payum\Payex\Action\Api\CreateAgreementAction;
use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Request\Api\CreateAgreement;

class CreateAgreementActionTest extends \PHPUnit\Framework\TestCase
{
    protected $requiredFields = array(
        'merchantRef' => 'aMerchRef',
        'description' => 'aDesc',
        'purchaseOperation' => AgreementApi::PURCHASEOPERATION_SALE,
        'maxAmount' => 100000,
        'startDate' => '',
        'stopDate' => '',
    );

    protected $requiredNotEmptyFields = array(
        'merchantRef' => 'aMerchRef',
        'description' => 'aDesc',
        'maxAmount' => 100000,
    );

    public function provideRequiredFields()
    {
        $fields = array();

        foreach ($this->requiredFields as $name => $value) {
            $fields[] = array($name);
        }

        return $fields;
    }

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
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\CreateAgreementAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\CreateAgreementAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function throwOnTryingSetNotAgreementApiAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\AgreementApi');
        $action = new CreateAgreementAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportCreateAgreementRequestWithArrayAccessAsModel()
    {
        $action = new CreateAgreementAction();

        $this->assertTrue($action->supports(new CreateAgreement($this->createMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCreateAgreementRequest()
    {
        $action = new CreateAgreementAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCreateAgreementRequestWithNotArrayAccessModel()
    {
        $action = new CreateAgreementAction();

        $this->assertFalse($action->supports(new CreateAgreement(new \stdClass())));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CreateAgreementAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @dataProvider provideRequiredFields
     */
    public function throwIfTryInitializeWithRequiredFieldNotPresent($requiredField)
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        unset($this->requiredFields[$requiredField]);

        $action = new CreateAgreementAction();

        $action->execute(new CreateAgreement($this->requiredFields));
    }

    /**
     * @test
     *
     * @dataProvider provideRequiredNotEmptyFields
     */
    public function throwIfTryInitializeWithRequiredFieldEmpty($requiredField)
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $fields = $this->requiredNotEmptyFields;

        $fields[$requiredField] = '';

        $action = new CreateAgreementAction();

        $action->execute(new CreateAgreement($fields));
    }

    /**
     * @test
     */
    public function shouldCreateAgreementPayment()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('create')
            ->with($this->requiredFields)
            ->willReturn(array(
                'agreementRef' => 'theRef',
            ));

        $action = new CreateAgreementAction();
        $action->setApi($apiMock);

        $request = new CreateAgreement($this->requiredFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame('theRef', $model['agreementRef']);
    }

    /**
     * @test
     */
    public function throwIfTryCreateAlreadyCreatedAgreement()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The agreement has already been created.');
        $action = new CreateAgreementAction();

        $request = new CreateAgreement(array(
            'agreementRef' => 'aRef',
        ));

        $action->execute($request);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Payum\Payex\Api\AgreementApi
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Payex\Api\AgreementApi', array(), array(), '', false);
    }
}
