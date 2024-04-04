<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateBillingAgreementAction;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateBillingAgreement;

class CreateBillingAgreementActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(CreateBillingAgreementAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface()
    {
        $rc = new \ReflectionClass(CreateBillingAgreementAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldSupportCreateBillingAgreementRequestAndArrayAccessAsModel()
    {
        $action = new CreateBillingAgreementAction();

        $this->assertTrue($action->supports(new CreateBillingAgreement($this->createMock('ArrayAccess'))));
    }

    public function testShouldNotSupportAnythingNotCreateBillingAgreementRequest()
    {
        $action = new CreateBillingAgreementAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new CreateBillingAgreementAction();

        $action->execute(new \stdClass());
    }

    public function testThrowIfTokenNotSetInModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('TOKEN must be set. Have you run SetExpressCheckoutAction?');
        $action = new CreateBillingAgreementAction();

        $action->execute(new CreateBillingAgreement(array()));
    }

    public function testShouldCallApiCreateBillingAgreementMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('createBillingAgreement')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TOKEN', $fields);
                $testCase->assertSame('theToken', $fields['TOKEN']);

                return array();
            })
        ;

        $action = new CreateBillingAgreementAction();
        $action->setApi($apiMock);

        $request = new CreateBillingAgreement(array(
            'TOKEN' => 'theToken',
        ));

        $action->execute($request);
    }

    public function testShouldCallApiCreateBillingMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('createBillingAgreement')
            ->willReturnCallback(function () {
                return array(
                    'FIRSTNAME' => 'theFirstname',
                    'EMAIL' => 'the@example.com',
                );
            })
        ;

        $action = new CreateBillingAgreementAction();
        $action->setApi($apiMock);

        $request = new CreateBillingAgreement(array(
            'TOKEN' => 'aToken',
        ));

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayHasKey('FIRSTNAME', $model);
        $this->assertSame('theFirstname', $model['FIRSTNAME']);

        $this->assertArrayHasKey('EMAIL', $model);
        $this->assertSame('the@example.com', $model['EMAIL']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}
