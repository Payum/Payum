<?php
namespace Payum\Stripe\Tests\Request\Api;

use Payum\Stripe\Request\Api\ObtainTokenRequest;

class ObtainTokenRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelAware()
    {
        $rc = new \ReflectionClass('Payum\Stripe\Request\Api\ObtainTokenRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseModelAware'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModelAsFirstArgument()
    {
        new ObtainTokenRequest($model = array());
    }
}