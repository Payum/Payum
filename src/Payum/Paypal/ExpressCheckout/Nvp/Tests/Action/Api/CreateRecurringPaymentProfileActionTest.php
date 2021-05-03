<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateRecurringPaymentProfileAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateRecurringPaymentProfile;

class CreateRecurringPaymentProfileActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(CreateRecurringPaymentProfileAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApoAwareInterface()
    {
        $rc = new \ReflectionClass(CreateRecurringPaymentProfileAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldCreateRecurringPaymentProfileRequestAndArrayAccessAsModel()
    {
        $action = new CreateRecurringPaymentProfileAction();

        $this->assertTrue($action->supports(new CreateRecurringPaymentProfile($this->createMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCreateRecurringPaymentProfileRequest()
    {
        $action = new CreateRecurringPaymentProfileAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CreateRecurringPaymentProfileAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfTokenNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The TOKEN, PROFILESTARTDATE, DESC, BILLINGPERIOD, BILLINGFREQUENCY, AMT, CURRENCYCODE fields are required.');
        $action = new CreateRecurringPaymentProfileAction();

        $action->execute(new CreateRecurringPaymentProfile(array()));
    }

    /**
     * @test
     */
    public function throwIfRequiredFieldMissing()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The PROFILESTARTDATE, DESC, BILLINGPERIOD, BILLINGFREQUENCY, AMT, CURRENCYCODE fields are required.');
        $action = new CreateRecurringPaymentProfileAction();

        $action->execute(new CreateRecurringPaymentProfile(array(
            'TOKEN' => 'aToken',
        )));
    }

    /**
     * @test
     */
    public function shouldCallApiCreateRecurringPaymentsProfileMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('createRecurringPaymentsProfile')
            ->will($this->returnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TOKEN', $fields);
                $testCase->assertEquals('theToken', $fields['TOKEN']);

                $testCase->assertArrayHasKey('PROFILESTARTDATE', $fields);
                $testCase->assertEquals('theStartDate', $fields['PROFILESTARTDATE']);

                $testCase->assertArrayHasKey('DESC', $fields);
                $testCase->assertEquals('theDesc', $fields['DESC']);

                $testCase->assertArrayHasKey('BILLINGPERIOD', $fields);
                $testCase->assertEquals('thePeriod', $fields['BILLINGPERIOD']);

                $testCase->assertArrayHasKey('BILLINGFREQUENCY', $fields);
                $testCase->assertEquals('theFrequency', $fields['BILLINGFREQUENCY']);

                $testCase->assertArrayHasKey('AMT', $fields);
                $testCase->assertEquals('theAmt', $fields['AMT']);

                $testCase->assertArrayHasKey('CURRENCYCODE', $fields);
                $testCase->assertEquals('theCurr', $fields['CURRENCYCODE']);

                $testCase->assertArrayHasKey('EMAIL', $fields);
                $testCase->assertEquals('theEmail', $fields['EMAIL']);

                $testCase->assertArrayHasKey('STREET', $fields);
                $testCase->assertEquals('theStreet', $fields['STREET']);

                $testCase->assertArrayHasKey('CITY', $fields);
                $testCase->assertEquals('theCity', $fields['CITY']);

                $testCase->assertArrayHasKey('COUNTRYCODE', $fields);
                $testCase->assertEquals('theCountry', $fields['COUNTRYCODE']);

                $testCase->assertArrayHasKey('ZIP', $fields);
                $testCase->assertEquals('theZip', $fields['ZIP']);

                return array();
            }))
        ;

        $action = new CreateRecurringPaymentProfileAction();
        $action->setApi($apiMock);

        $request = new CreateRecurringPaymentProfile(array(
            'TOKEN' => 'theToken',
            'PROFILESTARTDATE' => 'theStartDate',
            'DESC' => 'theDesc',
            'BILLINGPERIOD' => 'thePeriod',
            'BILLINGFREQUENCY' => 'theFrequency',
            'AMT' => 'theAmt',
            'CURRENCYCODE' => 'theCurr',
            'EMAIL' => 'theEmail',
            'STREET' => 'theStreet',
            'CITY' => 'theCity',
            'COUNTRYCODE' => 'theCountry',
            'ZIP' => 'theZip',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiCreateRecurringPaymentsProfileMethodAndUpdateModelFromResponse()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('createRecurringPaymentsProfile')
            ->will($this->returnCallback(function () {
                return array(
                    'PROFILEID' => 'theId',
                    'PROFILESTATUS' => 'theStatus',
                );
            }))
        ;

        $action = new CreateRecurringPaymentProfileAction();
        $action->setApi($apiMock);

        $request = new CreateRecurringPaymentProfile(array(
            'TOKEN' => 'theToken',
            'PROFILESTARTDATE' => 'theStartDate',
            'DESC' => 'theDesc',
            'BILLINGPERIOD' => 'thePeriod',
            'BILLINGFREQUENCY' => 'theFrequency',
            'AMT' => 'theAmt',
            'CURRENCYCODE' => 'theCurr',
            'EMAIL' => 'theEmail',
            'STREET' => 'theStreet',
            'CITY' => 'theCity',
            'COUNTRYCODE' => 'theCountry',
            'ZIP' => 'theZip',
        ));

        $action->execute($request);

        $model = $request->getModel();
        $this->assertArrayHasKey('PROFILEID', $model);
        $this->assertEquals('theId', $model['PROFILEID']);

        $this->assertArrayHasKey('PROFILESTATUS', $model);
        $this->assertEquals('theStatus', $model['PROFILESTATUS']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
