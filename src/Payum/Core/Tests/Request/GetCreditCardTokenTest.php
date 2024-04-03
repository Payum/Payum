<?php

namespace Payum\Core\Tests\Request;

use ArrayObject;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetCreditCardToken;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GetCreditCardTokenTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(GetCreditCardToken::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testShouldAllowGetModelSetInConstructor(): void
    {
        $model = new ArrayObject();

        $request = new GetCreditCardToken($model);

        $this->assertSame($model, $request->getModel());
    }

    public function shouldAllowSetAndLaterGetToken(): void
    {
        $request = new GetCreditCardToken([]);
        $request->token = 'aToken';

        $this->assertSame('aToken', $request->token);
    }
}
