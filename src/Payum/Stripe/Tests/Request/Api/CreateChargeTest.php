<?php
namespace Payum\Stripe\Tests\Request\Api;

use Payum\Stripe\Request\Api\CreateCharge;

class CreateChargeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Stripe\Request\Api\CreateCharge');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModelAsFirstArgument()
    {
        new CreateCharge($model = array());
    }
}
