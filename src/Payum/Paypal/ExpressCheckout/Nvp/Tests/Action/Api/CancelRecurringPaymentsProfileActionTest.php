<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CancelRecurringPaymentsProfileAction;
use Payum\Core\Tests\GenericActionTest;

class CancelRecurringPaymentsProfileActionTest extends GenericActionTest
{
    /**
     * @var Generic
     */
    protected $requestClass = 'Payum\Core\Request\Cancel';

    /**
     * @var ActionInterface
     */
    protected $actionClass = 'Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CancelRecurringPaymentsProfileAction';

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(array('BILLINGPERIOD' => 'foo'))),
            array(new $this->requestClass(new \ArrayObject(array('BILLINGPERIOD' => 'foo'))))
        );
    }
    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array(new $this->requestClass('foo')),
            array(new $this->requestClass(new \stdClass())),
            array($this->getMockForAbstractClass(Generic::class, array(array()))),
        );
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass($this->actionClass);

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CancelRecurringPaymentsProfileAction();
    }

    /**
     * @test
     */
    public function shouldSupportManageRecurringPaymentsProfileStatusRequestAndArrayAccessAsModel()
    {
        $action = new CancelRecurringPaymentsProfileAction();

        $this->assertTrue(
            $action->supports(new Cancel(array('BILLINGPERIOD' => 'foo')))
        );

        $this->assertTrue(
            $action->supports(new Cancel(new \ArrayObject(array('BILLINGPERIOD' => 'foo'))))
        );
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotManageRecurringPaymentsProfileStatusRequest()
    {
        $action = new CancelRecurringPaymentsProfileAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute($request = null)
    {
        $action = new CancelRecurringPaymentsProfileAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The PROFILEID fields are required.
     */
    public function throwIfProfileIdNotSetInModel()
    {
        $action = new CancelRecurringPaymentsProfileAction();

        $request = new Cancel(array('BILLINGPERIOD' => 'foo'));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiManageRecurringPaymentsProfileStatusMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('manageRecurringPaymentsProfileStatus')
            ->will($this->returnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('PROFILEID', $fields);
                $testCase->assertEquals('theProfileId', $fields['PROFILEID']);

                $testCase->assertArrayHasKey('ACTION', $fields);
                $testCase->assertEquals('Cancel', $fields['ACTION']);

                $testCase->assertArrayHasKey('BILLINGPERIOD', $fields);
                $testCase->assertEquals('theBillingPeriod', $fields['BILLINGPERIOD']);

                return array();
            }))
        ;

        $action = new CancelRecurringPaymentsProfileAction();
        $action->setApi($apiMock);

        $request = new Cancel(array(
            'PROFILEID' => 'theProfileId',
            'ACTION' => 'theAction',
            'BILLINGPERIOD' => 'theBillingPeriod'
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiManageRecurringPaymentsProfileStatusMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('manageRecurringPaymentsProfileStatus')
            ->will($this->returnCallback(function () {
                return array(
                    'PROFILEID' => 'theResponseProfileId',
                );
            }))
        ;

        $action = new CancelRecurringPaymentsProfileAction();
        $action->setApi($apiMock);

        $request = new Cancel(array(
            'PROFILEID' => 'aProfileId',
            'ACTION' => 'anAction',
            'BILLINGPERIOD' => 'theBillingPeriod'
        ));

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('PROFILEID', $model);
        $this->assertEquals('theResponseProfileId', $model['PROFILEID']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
