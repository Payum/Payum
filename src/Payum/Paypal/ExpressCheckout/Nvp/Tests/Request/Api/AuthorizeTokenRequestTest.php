<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeTokenRequest;

class AuthorizeTokenRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelAware()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeTokenRequest');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseModelAware'));
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultForceSetToFalseByDefault()
    {
        $request = new AuthorizeTokenRequest(new \stdClass);
        
        $this->assertFalse($request->isForced());
    }

    /**
     * @test
     */
    public function shouldAllowGetForceSetInConstructor()
    {
        $request = new AuthorizeTokenRequest(new \stdClass(), $force = true);

        $this->assertTrue($request->isForced());
    }
}