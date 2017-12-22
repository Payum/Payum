<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Authorize;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;

class AuthorizeTest extends TestCase
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
