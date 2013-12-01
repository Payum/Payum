<?php
namespace Payum\Payex\Tests\Action\Api;

use Payum\Payex\Action\Api\StopRecurringPaymentAction;
use Payum\Payex\Request\Api\StopRecurringPaymentRequest;

class StopRecurringPaymentActionTest extends \PHPUnit_Framework_TestCase
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

        $this->assertTrue($rc->isSubclassOf('Payum\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new StopRecurringPaymentAction;
    }

    /**
     * @test
     */
    public function shouldAllowSetRecurringApiAsApi()
    {
        $recurringApi = $this->getMock('Payum\Payex\Api\RecurringApi', array(), array(), '', false);
        
        $action = new StopRecurringPaymentAction;

        $action->setApi($recurringApi);
        
        $this->assertAttributeSame($recurringApi, 'api', $action);
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     * @expectedExceptionMessage Expected api must be instance of RecurringApi.
     */
    public function throwOnTryingSetNotRecurringApiAsApi()
    {
        $action = new StopRecurringPaymentAction;

        $action->setApi(new \stdClass);
    }

    /**
     * @test
     */
    public function shouldSupportStopRecurringPaymentRequestWithArrayAccessAsModel()
    {
        $action = new StopRecurringPaymentAction();

        $this->assertTrue($action->supports(new StopRecurringPaymentRequest($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotStopRecurringPaymentRequest()
    {
        $action = new StopRecurringPaymentAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportStopRecurringPaymentRequestWithNotArrayAccessModel()
    {
        $action = new StopRecurringPaymentAction;

        $this->assertFalse($action->supports(new StopRecurringPaymentRequest(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new StopRecurringPaymentAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @test
     * 
     * @dataProvider provideRequiredFields
     * 
     * @expectedException \Payum\Core\Exception\LogicException
     */
    public function throwIfTryInitializeWithRequiredFieldNotPresent($requiredField)
    {
        unset($this->requiredFields[$requiredField]);

        $action = new StopRecurringPaymentAction();

        $action->execute(new StopRecurringPaymentRequest($this->requiredFields));
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
            ->will($this->returnValue(array(
                'errorCode' => 'theCode',
            )));
        ;

        $action = new StopRecurringPaymentAction();
        $action->setApi($apiMock);

        $request = new StopRecurringPaymentRequest($this->requiredFields);
        
        $action->execute($request);

        $model = $request->getModel();
        $this->assertEquals('theCode', $model['errorCode']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Payex\Api\RecurringApi
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Payex\Api\RecurringApi', array(), array(), '', false);
    }
}