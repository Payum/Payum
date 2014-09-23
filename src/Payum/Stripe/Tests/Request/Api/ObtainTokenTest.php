<?php
namespace Payum\Stripe\Tests\Request\Api;

use Payum\Stripe\Request\Api\ObtainToken;

class ObtainTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Stripe\Request\Api\ObtainToken');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModelAsFirstArgument()
    {
        new ObtainToken($model = array());
    }
}