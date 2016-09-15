<?php
namespace Payum\Stripe\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetCreditCardToken;
use Payum\Core\Tests\GenericActionTest;
use Payum\Stripe\Action\GetCreditCardTokenAction;

class GetCreditCardTokenActionTest extends GenericActionTest
{
    protected $requestClass = GetCreditCardToken::class;

    protected $actionClass = GetCreditCardTokenAction::class;

    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(GetCreditCardTokenAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldDoNothingIfPaymentHasNoCustomerSet()
    {
        $model = [
        ];

        $action = new GetCreditCardTokenAction();

        $action->execute($getCreditCardToken = new GetCreditCardToken($model));

        self::assertEmpty($getCreditCardToken->token);
    }

    /**
     * @test
     */
    public function shouldSetCustomerIdAsCardToken()
    {
        $model = [
            'customer' => 'theToken',
        ];

        $action = new GetCreditCardTokenAction();

        $action->execute($getCreditCardToken = new GetCreditCardToken($model));

        self::assertEquals('theToken', $getCreditCardToken->token);
    }
}
