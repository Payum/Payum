<?php
namespace Payum\Paypal\Masspay\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\Masspay\Nvp\Request\Api\Masspay;

class MasspayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(Masspay::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModelAsArgument()
    {
        new Masspay(new \stdClass());
    }
}
