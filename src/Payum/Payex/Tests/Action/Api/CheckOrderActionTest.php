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

    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\CheckOrderAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\CheckOrderAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    public function testThrowOnTryingSetNotOrderApiAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\OrderApi');
        $action = new CheckOrderAction();

        $action->setApi(new \stdClass());
    }

    public function testShouldSupportCheckOrderRequestWithArrayAccessAsModel()
    {
        $action = new CheckOrderAction();

        $this->assertTrue($action->supports(new CheckOrder($this->createMock('ArrayAccess'))));
    }

    public function testShouldNotSupportAnythingNotCheckOrderRequest()
    {
        $action = new CheckOrderAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportCheckOrderRequestWithNotArrayAccessModel()
    {
        $action = new CheckOrderAction();

        $this->assertFalse($action->supports(new CheckOrder(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CheckOrderAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @dataProvider provideRequiredFields
     */
    public function testThrowIfTryInitializeWithRequiredFieldNotPresent($requiredField)
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        unset($this->requiredFields[$requiredField]);

        $action = new CheckOrderAction();

        $action->execute(new CheckOrder($this->requiredFields));
    }

    public function testShouldCompletePayment()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('check')
            ->with($this->requiredFields)
            ->willReturn(array(
                'transactionStatus' => 'theStatus',
            ));

        $action = new CheckOrderAction();
        $action->setApi($apiMock);

        $request = new CheckOrder($this->requiredFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame('theStatus', $model['transactionStatus']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Payex\Api\OrderApi
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Payex\Api\OrderApi', array(), array(), '', false);
    }
}
