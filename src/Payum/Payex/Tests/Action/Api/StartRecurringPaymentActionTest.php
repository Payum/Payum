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

    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\StartRecurringPaymentAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\StartRecurringPaymentAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function throwOnTryingSetNotRecurringApiAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\RecurringApi');
        $action = new StartRecurringPaymentAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportStartRecurringPaymentRequestWithArrayAccessAsModel()
    {
        $action = new StartRecurringPaymentAction();

        $this->assertTrue($action->supports(new StartRecurringPayment($this->createMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotStartRecurringPaymentRequest()
    {
        $action = new StartRecurringPaymentAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportStartRecurringPaymentRequestWithNotArrayAccessModel()
    {
        $action = new StartRecurringPaymentAction();

        $this->assertFalse($action->supports(new StartRecurringPayment(new \stdClass())));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new StartRecurringPaymentAction($this->createApiMock());

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

        $action = new StartRecurringPaymentAction();

        $action->execute(new StartRecurringPayment($this->requiredFields));
    }

    /**
     * @test
     */
    public function shouldStartRecurringPayment()
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
     * @return \PHPUnit\Framework\MockObject\MockObject|\Payum\Payex\Api\RecurringApi
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Payex\Api\RecurringApi', array(), array(), '', false);
    }
}
