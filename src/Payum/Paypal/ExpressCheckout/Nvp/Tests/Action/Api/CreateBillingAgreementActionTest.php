<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateBillingAgreementAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateBillingAgreement;

class CreateBillingAgreementActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(CreateBillingAgreementAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementApoAwareInterface()
    {
        $rc = new \ReflectionClass(CreateBillingAgreementAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldSupportCreateBillingAgreementRequestAndArrayAccessAsModel()
    {
        $action = new CreateBillingAgreementAction();

        $this->assertTrue($action->supports(new CreateBillingAgreement($this->createMock('ArrayAccess'))));
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
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CreateBillingAgreementAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfTokenNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('TOKEN must be set. Have you run SetExpressCheckoutAction?');
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
     * @return \PHPUnit\Framework\MockObject\MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
