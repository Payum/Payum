<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use Invit\PayumSofortueberweisung\Request\Api\CreateTransaction;
use Invit\PayumSofortueberweisung\Action\Api\CreateTransactionAction;
use Invit\PayumSofortueberweisung\Action\Api\BaseApiAwareAction;
use Invit\PayumSofortueberweisung\Api;

class CreateTransactionActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass(CreateTransactionAction::class);

        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArgument()
    {
        new CreateTransactionAction();
    }

    /**
     * @test
     */
    public function shouldSupportAuthorizeTokenRequestWithArrayAccessAsModel()
    {
        $action = new CreateTransactionAction();

        $this->assertTrue($action->supports(new CreateTransaction($this->getMock('ArrayAccess'))));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotAuthorizeTokenRequest()
    {
        $action = new CreateTransactionAction($this->createApiMock());

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new CreateTransactionAction($this->createApiMock());

        $action->execute(new \stdClass());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock(Api::class, array(), array(), '', false);
    }
}
