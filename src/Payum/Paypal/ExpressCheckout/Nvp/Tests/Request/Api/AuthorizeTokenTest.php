<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeToken;

class AuthorizeTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeToken');

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultForceSetToFalseByDefault()
    {
        $request = new AuthorizeToken(new \stdClass());

        $this->assertFalse($request->isForced());
    }

    /**
     * @test
     */
    public function shouldAllowGetForceSetInConstructor()
    {
        $request = new AuthorizeToken(new \stdClass(), $force = true);

        $this->assertTrue($request->isForced());
    }
}
