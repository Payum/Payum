<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\PaymentInterface;
use Payum\Core\Request\Http\GetRequestRequest;
use Payum\Core\Request\Http\ResponseInteractiveRequest;
use Payum\Core\Request\RenderTemplateRequest;
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
    public function shouldRenderExpectedTemplateIfHttpRequestNotPOST()
    {
        $model = array();
        $templateName = 'theTemplateName';
        $publishableKey ='thePubKey';

        $testCase = $this;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Http\GetRequestRequest'))
            ->will($this->returnCallback(function(GetRequestRequest $request) {
                $request->method = 'GET';
            }))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\RenderTemplateRequest'))
            ->will($this->returnCallback(function(RenderTemplateRequest $request) use ($templateName, $publishableKey, $model, $testCase) {
                $testCase->assertEquals($templateName, $request->getTemplateName());

                $context = $request->getContext();
                $testCase->assertArrayHasKey('model', $context);
                $testCase->assertArrayHasKey('publishable_key', $context);
                $testCase->assertEquals($publishableKey, $context['publishable_key']);

                $request->setResult('theContent');
            }))
        ;

        $action = new ObtainTokenAction($templateName);
        $action->setPayment($paymentMock);
        $action->setApi(new Keys($publishableKey, 'secretKey'));

        try {
            $action->execute(new ObtainTokenRequest($model));
        } catch (ResponseInteractiveRequest $interactiveRequest) {
            $this->assertEquals('theContent', $interactiveRequest->getContent());

            return;
        }


        $this->fail('Response interactive request was expected to be thrown.');
    }

    /**
     * @test
     */
    public function shouldRenderTemplateIfHttpRequestPOSTButNotContainStripeToken()
    {
        $model = array();
        $templateName = 'aTemplateName';
        $publishableKey ='aPubKey';

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Http\GetRequestRequest'))
            ->will($this->returnCallback(function(GetRequestRequest $request) {
                $request->method = 'POST';
            }))
        ;
        $paymentMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\RenderTemplateRequest'))
        ;

        $action = new ObtainTokenAction($templateName);
        $action->setPayment($paymentMock);
        $action->setApi(new Keys($publishableKey, 'secretKey'));

        try {
            $action->execute(new ObtainTokenRequest($model));
        } catch (ResponseInteractiveRequest $interactiveRequest) {
            return;
        }

        $this->fail('Response interactive request was expected to be thrown.');
    }

    /**
     * @test
     */
    public function shouldSetTokenFromHttpRequestToObtainTokenRequestOnPOST()
    {
        $model = array();
        $templateName = 'aTemplateName';
        $publishableKey ='aPubKey';

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\Http\GetRequestRequest'))
            ->will($this->returnCallback(function(GetRequestRequest $request) {
                $request->method = 'POST';
                $request->request = array('stripeToken' => 'theToken');
            }))
        ;

        $action = new ObtainTokenAction($templateName);
        $action->setPayment($paymentMock);
        $action->setApi(new Keys($publishableKey, 'secretKey'));

        $action->execute($obtainTokenRequest = new ObtainTokenRequest($model));

        $model = $obtainTokenRequest->getModel();
        $this->assertEquals('theToken', $model['card']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }

}