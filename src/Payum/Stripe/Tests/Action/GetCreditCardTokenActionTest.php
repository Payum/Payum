<?php
namespace Payum\Stripe\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetCreditCardToken;
use Payum\Core\Tests\GenericActionTest;
use Payum\Stripe\Action\GetCreditCardTokenAction;

class GetCreditCardTokenActionTest extends GenericActionTest
{
    protected $requestClass = GetCreditCardToken::class;

    protected $actionClass = GetCreditCardTokenAction::class;

    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(GetCreditCardTokenAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldDoNothingIfPaymentHasNoCustomerSet()
    {
        $model = [
        ];

        $action = new GetCreditCardTokenAction();

        $action->execute($getCreditCardToken = new GetCreditCardToken($model));

        $this->assertEmpty($getCreditCardToken->token);
    }

    public function testShouldSetCustomerIdAsCardToken()
    {
        $model = [
            'customer' => 'theToken',
        ];

        $action = new GetCreditCardTokenAction();

        $action->execute($getCreditCardToken = new GetCreditCardToken($model));

        $this->assertSame('theToken', $getCreditCardToken->token);
    }
}
