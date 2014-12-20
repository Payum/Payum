<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetExpressCheckoutDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetExpressCheckoutDetails;

class GetExpressCheckoutDetailsActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetExpressCheckoutDetailsAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new GetExpressCheckoutDetailsAction();
    }

    /**
     * @test
     */
    public function shouldSupportGetExpressCheckoutDetailsRequestAndArrayAccessAsModel()
    {
        $action = new GetExpressCheckoutDetailsAction();

        $this->assertTrue(
            $action->supports(new GetExpressCheckoutDetails($this->getMock('ArrayAccess')))
        );
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotGetExpressCheckoutDetailsRequest()
    {
        $action = new GetExpressCheckoutDetailsAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new GetExpressCheckoutDetailsAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage TOKEN must be set. Have you run SetExpressCheckoutAction?
     */
    public function throwIfTokenNotSetInModel()
    {
        $action = new GetExpressCheckoutDetailsAction();

        $request = new GetExpressCheckoutDetails(array());

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiGetExpressCheckoutDetailsMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getExpressCheckoutDetails')
            ->will($this->returnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TOKEN', $fields);
                $testCase->assertEquals('theToken', $fields['TOKEN']);

                return array();
            }))
        ;

        $action = new GetExpressCheckoutDetailsAction();
        $action->setApi($apiMock);

        $request = new GetExpressCheckoutDetails(array(
            'TOKEN' => 'theToken',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiGetExpressCheckoutDetailsMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('getExpressCheckoutDetails')
            ->will($this->returnCallback(function () {
                return array(
                    'FIRSTNAME' => 'theFirstname',
                    'EMAIL' => 'the@example.com',
                );
            }))
        ;

        $action = new GetExpressCheckoutDetailsAction();
        $action->setApi($apiMock);

        $request = new GetExpressCheckoutDetails(array(
            'TOKEN' => 'aToken',
        ));

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('FIRSTNAME', $model);
        $this->assertEquals('theFirstname', $model['FIRSTNAME']);

        $this->assertArrayHasKey('EMAIL', $model);
        $this->assertEquals('the@example.com', $model['EMAIL']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
