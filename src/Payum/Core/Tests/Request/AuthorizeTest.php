<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Authorize;
use Payum\Core\Request\Generic;

class AuthorizeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(Authorize::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModel()
    {
        new Authorize(new \stdClass());
    }
}
