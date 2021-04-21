<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Cancel;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;

class CancelTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(Cancel::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModel()
    {
        new Cancel(new \stdClass());
    }
}
