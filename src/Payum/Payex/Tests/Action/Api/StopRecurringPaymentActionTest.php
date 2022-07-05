<?php

namespace Payum\Payex\Tests\Action\Api;

use Payum\Payex\Action\Api\StopRecurringPaymentAction;
use Payum\Payex\Request\Api\StopRecurringPayment;

class StopRecurringPaymentActionTest extends \PHPUnit\Framework\TestCase
{
    protected $requiredFields = array(
        'agreementRef' => 'aRef',
    );

    public function provideRequiredFields()
    {
        $fields = array();

        foreach ($this->requiredFields as $name => $value) {
            $fields[] = array($name);
        }

        return $fields;
    }

    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\StopRecurringPaymentAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\StopRecurringPaymentAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function throwOnTryingSetNotRecurringApiAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\RecurringApi');
        $action = new StopRecurringPaymentAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportStopRecurringPaymentRequestWithArrayAccessAsModel()
    {
        $action = new StopRecurringPaymentAction();

        $this->assertTrue($action->supports(new StopRecurringPayment($this->createMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotStopRecurringPaymentRequest()
    {
        $action = new StopRecurringPaymentAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportStopRecurringPaymentRequestWithNotArrayAccessModel()
    {
        $action = new StopRecurringPaymentAction();

        $this->assertFalse($action->supports(new StopRecurringPayment(new \stdClass())));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new StopRecurringPaymentAction($this->createApiMock());

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

        $action = new StopRecurringPaymentAction();

        $action->execute(new StopRecurringPayment($this->requiredFields));
    }

    /**
     * @test
     */
    public function shouldStopRecurringPayment()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('stop')
            ->with($this->requiredFields)
            ->willReturn(array(
                'errorCode' => 'theCode',
            ));

        $action = new StopRecurringPaymentAction();
        $action->setApi($apiMock);

        $request = new StopRecurringPayment($this->requiredFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame('theCode', $model['errorCode']);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Payum\Payex\Api\RecurringApi
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Payex\Api\RecurringApi', array(), array(), '', false);
    }
}
