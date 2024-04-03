<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeToken;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class AuthorizeTokenTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(AuthorizeToken::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testShouldAllowGetDefaultForceSetToFalseByDefault(): void
    {
        $request = new AuthorizeToken(new stdClass());

        $this->assertFalse($request->isForced());
    }

    public function testShouldAllowGetForceSetInConstructor(): void
    {
        $request = new AuthorizeToken(new stdClass(), $force = true);

        $this->assertTrue($request->isForced());
    }
}
