<?php
namespace Payum\Payex\Tests\Action\Api;

use Payum\Payex\Action\Api\InitializeOrderAction;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Request\Api\InitializeOrder;
use Payum\Core\Reply\HttpRedirect;

class InitializeOrderActionTest extends \PHPUnit\Framework\TestCase
{
    protected $requiredFields = array(
        'price' => 1000,
        'priceArgList' => '',
        'vat' => 0,
        'currency' => 'NOK',
        'orderId' => 123,
        'productNumber' => 123,
        'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
        'view' => OrderApi::VIEW_CREDITCARD,
        'description' => 'a description',
        'additionalValues' => '',
        'returnUrl' => 'http://example.com/a_return_url',
        'cancelUrl' => 'http://example.com/a_cancel_url',
        'clientIPAddress' => '127.0.0.1',
        'clientIdentifier' => 'USER-AGENT=cli-php',
        'agreementRef' => '',
        'clientLanguage' => 'en-US',
    );

    public function provideRequiredFields()
    {
        $fields = array();

        foreach ($this->requiredFields as $name => $value) {
            $fields[] = array($name);
        }

        return $fields;
    }

    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\InitializeOrderAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Payex\Action\Api\InitializeOrderAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function throwOnTryingSetNotOrderApiAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Payex\Api\OrderApi');
        $action = new InitializeOrderAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSupportInitializeOrderRequestWithArrayAccessAsModel()
    {
        $action = new InitializeOrderAction();

        $this->assertTrue($action->supports(new InitializeOrder($this->createMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotInitializeOrderRequest()
    {
        $action = new InitializeOrderAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportInitializeOrderRequestWithNotArrayAccessModel()
    {
        $action = new InitializeOrderAction();

        $this->assertFalse($action->supports(new InitializeOrder(new \stdClass())));
    }

    /**
     * @test
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new InitializeOrderAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function throwIfTryInitializeAlreadyInitializedOrder()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The order has already been initialized.');
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('initialize')
        ;

        $action = new InitializeOrderAction();
        $action->setApi($apiMock);

        $action->execute(new InitializeOrder(array(
            'orderRef' => 'aRef',
        )));
    }

    /**
     * @test
     *
     * @dataProvider provideRequiredFields
     */
    public function throwIfTryInitializeWithRequiredFieldNotPresent($requiredField)
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        unset($this->requiredFields[$requiredField]);

        $action = new InitializeOrderAction();

        $action->execute(new InitializeOrder($this->requiredFields));
    }

    /**
     * @test
     */
    public function shouldInitializePayment()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('initialize')
            ->with($this->requiredFields)
            ->will($this->returnValue(array(
                'orderRef' => 'theRef',
            )));

        $action = new InitializeOrderAction();
        $action->setApi($apiMock);

        $request = new InitializeOrder($this->requiredFields);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertEquals('theRef', $model['orderRef']);
    }

    /**
     * @test
     */
    public function shouldThrowHttpRedirectReplyIfRedirectUrlReturnedInResponse()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('initialize')
            ->with($this->requiredFields)
            ->will($this->returnValue(array(
                'redirectUrl' => 'http://example.com/theUrl',
            )));

        $action = new InitializeOrderAction();
        $action->setApi($apiMock);

        $request = new InitializeOrder($this->requiredFields);

        try {
            $action->execute($request);
        } catch (HttpRedirect $reply) {
            $this->assertEquals('http://example.com/theUrl', $reply->getUrl());

            return;
        }

        $this->fail('The redirect url reply was expected to be thrown.');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Payex\Api\OrderApi
     */
    protected function createApiMock()
    {
        return $this->createMock('Payum\Payex\Api\OrderApi', array(), array(), '', false);
    }
}
