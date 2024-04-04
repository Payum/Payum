<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Core\Request\GetCreditCardToken;
use PHPUnit\Framework\TestCase;

class GetCreditCardTokenTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(GetCreditCardToken::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testShouldAllowGetModelSetInConstructor()
    {
        $model = new \ArrayObject();

        $request = new GetCreditCardToken($model);

        $this->assertSame($model, $request->getModel());
    }

    public function shouldAllowSetAndLaterGetToken()
    {
        $request = new GetCreditCardToken([]);
        $request->token = 'aToken';

        $this->assertSame('aToken', $request->token);
    }
}
