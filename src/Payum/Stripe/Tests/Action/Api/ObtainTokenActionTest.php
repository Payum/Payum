<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\PaymentInterface;
use Payum\Core\Request\Http\ResponseInteractiveRequest;
use Payum\Stripe\Action\Api\ObtainTokenAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\ObtainTokenRequest;

class ObtainTokenActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Stripe\Action\Api\ObtainTokenAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Stripe\Action\Api\ObtainTokenAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithTemplateAsFirstArgument()
    {
        new ObtainTokenAction('aTemplateName');
    }

    /**
     * @test
     */
    public function shouldAllowSetKeysAsApi()
    {
        $action = new ObtainTokenAction('aTemplateName');

        $action->setApi(new Keys('publishableKey', 'secretKey'));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwNotSupportedApiIfNotKeysGivenAsApi()
    {
        $action = new ObtainTokenAction('aTemplateName');

        $action->setApi('not keys instance');
    }

    /**
     * @test
     */
    public function shouldSupportObtainTokenRequestWithArrayAccessModel()
    {
        $action = new ObtainTokenAction('aTemplateName');

        $this->assertTrue($action->supports(new ObtainTokenRequest(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportObtainTokenRequestWithNotArrayAccessModel()
    {
        $action = new ObtainTokenAction('aTemplateName');

        $this->assertFalse($action->supports(new ObtainTokenRequest(new \stdClass)));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotObtainTokenRequest()
    {
        $action = new ObtainTokenAction('aTemplateName');

        $this->assertFalse($action->supports(new \stdClass));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The token has already been set.
     */
    public function throwIfModelAlreadyHaveTokenSet()
    {
        $action = new ObtainTokenAction('aTemplateName');

        $action->execute(new ObtainTokenRequest(array(
            'card' => 'aToken'
        )));
    }

    /**
     * @test
     */
    public function shouldRenderExpectedPageIfGetRequest()
    {
        $action = new ObtainTokenAction('aTemplateName');
        $action->setPayment($this->createPaymentMock());
        $action->setApi(new Keys('publishableKey', 'secretKey'));

        try {
            $action->execute(new ObtainTokenRequest(array()));
        } catch (ResponseInteractiveRequest $interactiveRequest) {


            return;
        }


        $this->fail('Response interactive request was expected to be thrown.');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }

}