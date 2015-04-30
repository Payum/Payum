<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Stripe\Action\Api\ObtainTokenAction;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\ObtainToken;

class ObtainTokenActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Stripe\Action\Api\ObtainTokenAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\GatewayAwareAction'));
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

        $this->assertTrue($action->supports(new ObtainToken(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportObtainTokenRequestWithNotArrayAccessModel()
    {
        $action = new ObtainTokenAction('aTemplateName');

        $this->assertFalse($action->supports(new ObtainToken(new \stdClass())));
    }

    /**
     * @test
     */
    public function shouldNotSupportNotObtainTokenRequest()
    {
        $action = new ObtainTokenAction('aTemplateName');

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     * @expectedExceptionMessage Action ObtainTokenAction is not supported the request stdClass.
     */
    public function throwRequestNotSupportedIfNotSupportedGiven()
    {
        $action = new ObtainTokenAction('aTemplateName');

        $action->execute(new \stdClass());
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

        $action->execute(new ObtainToken(array(
            'card' => 'aToken',
        )));
    }

    /**
     * @test
     */
    public function shouldRenderExpectedTemplateIfHttpRequestNotPOST()
    {
        $model = array();
        $templateName = 'theTemplateName';
        $publishableKey = 'thePubKey';

        $testCase = $this;

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHttpRequest'))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->method = 'GET';
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\RenderTemplate'))
            ->will($this->returnCallback(function (RenderTemplate $request) use ($templateName, $publishableKey, $model, $testCase) {
                $testCase->assertEquals($templateName, $request->getTemplateName());

                $context = $request->getParameters();
                $testCase->assertArrayHasKey('model', $context);
                $testCase->assertArrayHasKey('publishable_key', $context);
                $testCase->assertEquals($publishableKey, $context['publishable_key']);

                $request->setResult('theContent');
            }))
        ;

        $action = new ObtainTokenAction($templateName);
        $action->setGateway($gatewayMock);
        $action->setApi(new Keys($publishableKey, 'secretKey'));

        try {
            $action->execute(new ObtainToken($model));
        } catch (HttpResponse $reply) {
            $this->assertEquals('theContent', $reply->getContent());

            return;
        }

        $this->fail('HttpResponse reply was expected to be thrown.');
    }

    /**
     * @test
     */
    public function shouldRenderTemplateIfHttpRequestPOSTButNotContainStripeToken()
    {
        $model = array();
        $templateName = 'aTemplateName';
        $publishableKey = 'aPubKey';

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHttpRequest'))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->method = 'POST';
            }))
        ;
        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\RenderTemplate'))
        ;

        $action = new ObtainTokenAction($templateName);
        $action->setGateway($gatewayMock);
        $action->setApi(new Keys($publishableKey, 'secretKey'));

        try {
            $action->execute(new ObtainToken($model));
        } catch (HttpResponse $reply) {
            return;
        }

        $this->fail('HttpResponse reply was expected to be thrown.');
    }

    /**
     * @test
     */
    public function shouldSetTokenFromHttpRequestToObtainTokenRequestOnPOST()
    {
        $model = array();
        $templateName = 'aTemplateName';
        $publishableKey = 'aPubKey';

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\GetHttpRequest'))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->method = 'POST';
                $request->request = array('stripeToken' => 'theToken');
            }))
        ;

        $action = new ObtainTokenAction($templateName);
        $action->setGateway($gatewayMock);
        $action->setApi(new Keys($publishableKey, 'secretKey'));

        $action->execute($obtainToken = new ObtainToken($model));

        $model = $obtainToken->getModel();
        $this->assertEquals('theToken', $model['card']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock('Payum\Core\GatewayInterface');
    }
}
