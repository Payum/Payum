<?php
namespace Payum\Payex\Tests\Action\Api;

use Payum\Payex\Action\Api\CompleteOrderAction;
use Payum\Payex\Request\Api\CompleteOrder;

class CompleteOrderActionTest extends \PHPUnit_Framework_TestCase
{
    protected $requiredFields = array(
        'orderRef' => 'aRef',
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
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\CompleteOrderAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\CompleteOrderAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CompleteOrderAction();
    }

    /**
     * @test
     */
    public function shouldAllowSetOrderApiAsApi()
    {
        $orderApi = $this->getMock('Payum\Payex\Api\OrderApi', array(), array(), '', false);

        $action = new CompleteOrderAction();

        $action->setApi($orderApi);

        $this->assertAttributeSame($orderApi, 'api', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     * @expectedExceptionMessage Expected api must be instance of OrderApi.
     */
    public function throwOnTryingSetNotOrderApiAsApi()
    {
        $action = new CompleteOrderAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportCompleteOrderRequestWithArrayAccessAsModel()
    {
        $action = new CompleteOrderAction();

        $this->assertTrue($action->supports(new CompleteOrder($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCompleteOrderRequest()
    {
        $action = new CompleteOrderAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCompleteOrderRequestWithNotArrayAccessModel()
    {
        $action = new CompleteOrderAction();

        $this->assertFalse($action->supports(new CompleteOrder(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CompleteOrderAction($this->createApiMock());

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

        $action = new CompleteOrderAction();

        $action->execute(new CompleteOrder($this->requiredFields));
    }

    /**
     * @test
     */
    public function shouldCompletePayment()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('complete')
            ->with($this->requiredFields)
            ->will($this->returnValue(array(
                'transactionRef' => 'theRef',
            )));

        $action = new CompleteOrderAction();
        $action->setApi($apiMock);

        $request = new CompleteOrder($this->requiredFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertEquals('theRef', $model['transactionRef']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Payex\Api\OrderApi
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Payex\Api\OrderApi', array(), array(), '', false);
    }
}
