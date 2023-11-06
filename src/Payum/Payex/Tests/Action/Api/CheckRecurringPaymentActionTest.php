<?php
namespace Payum\Payex\Tests\Action\Api;

use Payum\Payex\Action\Api\CheckRecurringPaymentAction;
use Payum\Payex\Api\RecurringApi;
use Payum\Payex\Request\Api\CheckRecurringPayment;

class CheckRecurringPaymentActionTest extends \PHPUnit\Framework\TestCase
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

    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\CheckRecurringPaymentAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\CheckRecurringPaymentAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    public function testThrowOnTryingSetNotRecurringApiAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\RecurringApi');
        $action = new CheckRecurringPaymentAction();

        $action->setApi(new \stdClass());
    }

    public function testShouldSupportCheckRecurringPaymentRequestWithArrayAccessAsModel()
    {
        $action = new CheckRecurringPaymentAction();

        $this->assertTrue($action->supports(new CheckRecurringPayment($this->createMock('ArrayAccess'))));
    }

    public function testShouldNotSupportAnythingNotCheckRecurringPaymentRequest()
    {
        $action = new CheckRecurringPaymentAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportCheckRecurringPaymentRequestWithNotArrayAccessModel()
    {
        $action = new CheckRecurringPaymentAction();

        $this->assertFalse($action->supports(new CheckRecurringPayment(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CheckRecurringPaymentAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @dataProvider provideRequiredFields
     */
    public function testThrowIfTryInitializeWithRequiredFieldNotPresent($requiredField)
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        unset($this->requiredFields[$requiredField]);

        $action = new CheckRecurringPaymentAction();

        $action->execute(new CheckRecurringPayment($this->requiredFields));
    }

    public function testShouldCheckRecurringPayment()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('check')
            ->with($this->requiredFields)
            ->willReturn(array(
                'recurringStatus' => RecurringApi::RECURRINGSTATUS_STOPPEDBYCLIENT,
            ));

        $action = new CheckRecurringPaymentAction();
        $action->setApi($apiMock);

        $request = new CheckRecurringPayment($this->requiredFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame(RecurringApi::RECURRINGSTATUS_STOPPEDBYCLIENT, $model['recurringStatus']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Payex\Api\RecurringApi
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Payex\Api\RecurringApi', array(), array(), '', false);
    }
}
