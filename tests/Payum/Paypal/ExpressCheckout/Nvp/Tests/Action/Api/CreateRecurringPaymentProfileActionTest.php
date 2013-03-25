<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Buzz\Message\Form\FormRequest;

use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateRecurringPaymentProfileAction;
use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;
use Payum\Paypal\ExpressCheckout\Nvp\Model\RecurringPaymentDetails;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateRecurringPaymentProfileRequest;

class CreateRecurringPaymentProfileActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseActionApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateRecurringPaymentProfileAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseActionApiAware'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CreateRecurringPaymentProfileAction();
    }

    /**
     * @test
     */
    public function shouldCreateRecurringPaymentProfileRequestAndArrayAccessAsModel()
    {
        $action = new CreateRecurringPaymentProfileAction();

        $this->assertTrue($action->supports(new CreateRecurringPaymentProfileRequest($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldSupportCreateRecurringPaymentProfileRequestWithRecurringPaymentDetailsAsModel()
    {
        $action = new CreateRecurringPaymentProfileAction();

        $this->assertTrue($action->supports(new CreateRecurringPaymentProfileRequest(new RecurringPaymentDetails)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotDoExpressCheckoutPaymentRequest()
    {
        $action = new CreateRecurringPaymentProfileAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CreateRecurringPaymentProfileAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The TOKEN fields is required.
     */
    public function throwIfTokenNotSetInModel()
    {
        $action = new CreateRecurringPaymentProfileAction();

        $action->execute(new CreateRecurringPaymentProfileRequest(array()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The PROFILESTARTDATE fields is required.
     */
    public function throwIfRequiredFieldMissing()
    {
        $action = new CreateRecurringPaymentProfileAction();

        $action->execute(new CreateRecurringPaymentProfileRequest(array(
            'TOKEN' => 'aToken',
        )));
    }

    /**
     * @test
     */
    public function shouldCallApiCreateRecurringPaymentsProfileMethodWithExpectedRequiredArguments()
    {
        $actualRequest = null;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('createRecurringPaymentsProfile')
            ->will($this->returnCallback(function($request) use (&$actualRequest){
                $actualRequest = $request;

                return new Response();
            }))
        ;

        $action = new CreateRecurringPaymentProfileAction();
        $action->setApi($apiMock);

        $request = new CreateRecurringPaymentProfileRequest(array(
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

        $this->assertInstanceOf('Buzz\Message\Form\FormRequest', $actualRequest);

        $fields = $actualRequest->getFields();

        $this->assertArrayHasKey('TOKEN', $fields);
        $this->assertEquals('theToken', $fields['TOKEN']);

        $this->assertArrayHasKey('PROFILESTARTDATE', $fields);
        $this->assertEquals('theStartDate', $fields['PROFILESTARTDATE']);

        $this->assertArrayHasKey('DESC', $fields);
        $this->assertEquals('theDesc', $fields['DESC']);

        $this->assertArrayHasKey('BILLINGPERIOD', $fields);
        $this->assertEquals('thePeriod', $fields['BILLINGPERIOD']);

        $this->assertArrayHasKey('BILLINGFREQUENCY', $fields);
        $this->assertEquals('theFrequency', $fields['BILLINGFREQUENCY']);

        $this->assertArrayHasKey('AMT', $fields);
        $this->assertEquals('theAmt', $fields['AMT']);

        $this->assertArrayHasKey('CURRENCYCODE', $fields);
        $this->assertEquals('theCurr', $fields['CURRENCYCODE']);

        $this->assertArrayHasKey('EMAIL', $fields);
        $this->assertEquals('theEmail', $fields['EMAIL']);

        $this->assertArrayHasKey('STREET', $fields);
        $this->assertEquals('theStreet', $fields['STREET']);

        $this->assertArrayHasKey('CITY', $fields);
        $this->assertEquals('theCity', $fields['CITY']);

        $this->assertArrayHasKey('COUNTRYCODE', $fields);
        $this->assertEquals('theCountry', $fields['COUNTRYCODE']);

        $this->assertArrayHasKey('ZIP', $fields);
        $this->assertEquals('theZip', $fields['ZIP']);
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
            ->will($this->returnCallback(function() {
                $response = new Response;
                $response->setContent(http_build_query(array(
                    'PROFILEID'=> 'theId',
                    'PROFILESTATUS' => 'theStatus'
                )));

                return $response;
            }))
        ;

        $action = new CreateRecurringPaymentProfileAction();
        $action->setApi($apiMock);

        $request = new CreateRecurringPaymentProfileRequest(array(
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

        $this->assertArrayHasKey('PROFILEID', $request->getModel());
        $this->assertEquals('theId', $request->getModel()['PROFILEID']);
        
        $this->assertArrayHasKey('PROFILESTATUS', $request->getModel());
        $this->assertEquals('theStatus', $request->getModel()['PROFILESTATUS']);
    }

    /**
     * @test
     */
    public function shouldUpdateModelFromResponseInCaughtAckFailedException()
    {
        $response = new Response();
        $response->setContent(http_build_query(array(
            'L_ERRORCODE0' => 'foo_error',
            'L_ERRORCODE1' => 'bar_error',
        )));

        $ackFailedException = new HttpResponseAckNotSuccessException(new FormRequest(), $response);

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('createRecurringPaymentsProfile')
            ->will($this->throwException($ackFailedException))
        ;

        $action = new CreateRecurringPaymentProfileAction();
        $action->setApi($apiMock);

        $request = new CreateRecurringPaymentProfileRequest(array(
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

        $this->assertArrayHasKey('L_ERRORCODE0', $request->getModel());
        $this->assertEquals('foo_error', $request->getModel()['L_ERRORCODE0']);
        $this->assertArrayHasKey('L_ERRORCODE1', $request->getModel());
        $this->assertEquals('bar_error', $request->getModel()['L_ERRORCODE1']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}