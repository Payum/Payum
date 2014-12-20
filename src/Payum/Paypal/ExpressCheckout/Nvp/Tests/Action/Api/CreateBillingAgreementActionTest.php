<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateBillingAgreementAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateBillingAgreement;

class CreateBillingAgreementActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateBillingAgreementAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseApiAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CreateBillingAgreementAction();
    }

    /**
     * @test
     */
    public function shouldSupportCreateBillingAgreementRequestAndArrayAccessAsModel()
    {
        $action = new CreateBillingAgreementAction();

        $this->assertTrue($action->supports(new CreateBillingAgreement($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotCreateBillingAgreementRequest()
    {
        $action = new CreateBillingAgreementAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CreateBillingAgreementAction();

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
        $action = new CreateBillingAgreementAction();

        $action->execute(new CreateBillingAgreement(array()));
    }

    /**
     * @test
     */
    public function shouldCallApiCreateBillingAgreementMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('createBillingAgreement')
            ->will($this->returnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TOKEN', $fields);
                $testCase->assertEquals('theToken', $fields['TOKEN']);

                return array();
            }))
        ;

        $action = new CreateBillingAgreementAction();
        $action->setApi($apiMock);

        $request = new CreateBillingAgreement(array(
            'TOKEN' => 'theToken',
        ));

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiCreateBillingMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('createBillingAgreement')
            ->will($this->returnCallback(function () {
                return array(
                    'FIRSTNAME' => 'theFirstname',
                    'EMAIL' => 'the@example.com',
                );
            }))
        ;

        $action = new CreateBillingAgreementAction();
        $action->setApi($apiMock);

        $request = new CreateBillingAgreement(array(
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
