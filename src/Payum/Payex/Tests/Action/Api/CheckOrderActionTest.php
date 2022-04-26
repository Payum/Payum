<?php
namespace Payum\Payex\Tests\Action\Api;

use Payum\Payex\Action\Api\CheckOrderAction;
use Payum\Payex\Request\Api\CheckOrder;

class CheckOrderActionTest extends \PHPUnit\Framework\TestCase
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
    public function throwOnTryingSetNotOrderApiAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\OrderApi');
        $action = new CheckOrderAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportCheckOrderRequestWithArrayAccessAsModel()
    {
        $action = new CheckOrderAction();

        $this->assertTrue($action->supports(new CheckOrder($this->createMock('ArrayAccess'))));
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
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CheckOrderAction($this->createApiMock());

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
     * @return \PHPUnit\Framework\MockObject\MockObject|\Payum\Payex\Api\OrderApi
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Payex\Api\OrderApi', array(), array(), '', false);
    }
}
