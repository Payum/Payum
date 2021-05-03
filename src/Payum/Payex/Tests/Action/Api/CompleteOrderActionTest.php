<?php
namespace Payum\Payex\Tests\Action\Api;

use Payum\Payex\Action\Api\CompleteOrderAction;
use Payum\Payex\Request\Api\CompleteOrder;

class CompleteOrderActionTest extends \PHPUnit\Framework\TestCase
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
    public function throwOnTryingSetNotOrderApiAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\OrderApi');
        $action = new CompleteOrderAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportCompleteOrderRequestWithArrayAccessAsModel()
    {
        $action = new CompleteOrderAction();

        $this->assertTrue($action->supports(new CompleteOrder($this->createMock('ArrayAccess'))));
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
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CompleteOrderAction($this->createApiMock());

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
        return $this->createMock('Payum\Payex\Api\OrderApi', array(), array(), '', false);
    }
}
