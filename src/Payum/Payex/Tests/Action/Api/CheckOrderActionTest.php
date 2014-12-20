<?php
namespace Payum\Payex\Tests\Action\Api;

use Payum\Payex\Action\Api\CheckOrderAction;
use Payum\Payex\Request\Api\CheckOrder;

class CheckOrderActionTest extends \PHPUnit_Framework_TestCase
{
    protected $requiredFields = array(
        'transactionNumber' => 'aNum',
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
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\CheckOrderAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\CheckOrderAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CheckOrderAction();
    }

    /**
     * @test
     */
    public function shouldAllowSetOrderApiAsApi()
    {
        $orderApi = $this->getMock('Payum\Payex\Api\OrderApi', array(), array(), '', false);

        $action = new CheckOrderAction();

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
        $action = new CheckOrderAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportCheckOrderRequestWithArrayAccessAsModel()
    {
        $action = new CheckOrderAction();

        $this->assertTrue($action->supports(new CheckOrder($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCheckOrderRequest()
    {
        $action = new CheckOrderAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportCheckOrderRequestWithNotArrayAccessModel()
    {
        $action = new CheckOrderAction();

        $this->assertFalse($action->supports(new CheckOrder(new \stdClass())));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CheckOrderAction($this->createApiMock());

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

        $action = new CheckOrderAction();

        $action->execute(new CheckOrder($this->requiredFields));
    }

    /**
     * @test
     */
    public function shouldCompletePayment()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('check')
            ->with($this->requiredFields)
            ->will($this->returnValue(array(
                'transactionStatus' => 'theStatus',
            )));

        $action = new CheckOrderAction();
        $action->setApi($apiMock);

        $request = new CheckOrder($this->requiredFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertEquals('theStatus', $model['transactionStatus']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Payex\Api\OrderApi
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Payex\Api\OrderApi', array(), array(), '', false);
    }
}
