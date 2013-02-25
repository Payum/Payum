<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request;

use Payum\Paypal\ExpressCheckout\Nvp\Request\AuthorizeTokenRequest;

class AuthorizeTokenRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelRequest()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\AuthorizeTokenRequest');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Request\BaseModelRequest'));
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