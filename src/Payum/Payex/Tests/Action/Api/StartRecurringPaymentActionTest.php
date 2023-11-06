<?php
namespace Payum\Payex\Tests\Action\Api;

use Payum\Payex\Action\Api\StartRecurringPaymentAction;
use Payum\Payex\Api\RecurringApi;
use Payum\Payex\Request\Api\StartRecurringPayment;

class StartRecurringPaymentActionTest extends \PHPUnit\Framework\TestCase
{
    protected $requiredFields = array(
        'agreementRef' => 'aRef',
        'startDate' => '2013-10-10 12:21:21',
        'periodType' => RecurringApi::PERIODTYPE_HOURS,
        'period' => 2,
        'alertPeriod' => 0,
        'price' => 1000,
        'productNumber' => 'aProductNumber',
        'orderId' => 'anOrderId',
        'description' => 'aDesc',
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
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\StartRecurringPaymentAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\StartRecurringPaymentAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    public function testThrowOnTryingSetNotRecurringApiAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\RecurringApi');
        $action = new StartRecurringPaymentAction();

        $action->setApi(new \stdClass());
    }

    public function testShouldSupportStartRecurringPaymentRequestWithArrayAccessAsModel()
    {
        $action = new StartRecurringPaymentAction();

        $this->assertTrue($action->supports(new StartRecurringPayment($this->createMock('ArrayAccess'))));
    }

    public function testShouldNotSupportAnythingNotStartRecurringPaymentRequest()
    {
        $action = new StartRecurringPaymentAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testShouldNotSupportStartRecurringPaymentRequestWithNotArrayAccessModel()
    {
        $action = new StartRecurringPaymentAction();

        $this->assertFalse($action->supports(new StartRecurringPayment(new \stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new StartRecurringPaymentAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @dataProvider provideRequiredFields
     */
    public function testThrowIfTryInitializeWithRequiredFieldNotPresent($requiredField)
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        unset($this->requiredFields[$requiredField]);

        $action = new StartRecurringPaymentAction();

        $action->execute(new StartRecurringPayment($this->requiredFields));
    }

    public function testShouldStartRecurringPayment()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('start')
            ->with($this->requiredFields)
            ->willReturn(array(
                'recurringRef' => 'theRecRef',
            ));

        $action = new StartRecurringPaymentAction();
        $action->setApi($apiMock);

        $request = new StartRecurringPayment($this->requiredFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertSame('theRecRef', $model['recurringRef']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Payex\Api\RecurringApi
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Payex\Api\RecurringApi', array(), array(), '', false);
    }
}
