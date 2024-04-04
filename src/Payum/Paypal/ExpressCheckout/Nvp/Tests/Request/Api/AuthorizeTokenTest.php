<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeToken;

class AuthorizeTokenTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeToken');

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testShouldAllowGetDefaultForceSetToFalseByDefault()
    {
        $request = new AuthorizeToken(new \stdClass());

        $this->assertFalse($request->isForced());
    }

    public function testShouldAllowGetForceSetInConstructor()
    {
        $request = new AuthorizeToken(new \stdClass(), $force = true);

        $this->assertTrue($request->isForced());
    }
}
