<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Cancel;
use Payum\Core\Request\Generic;

class CancelTest extends \PHPUnit_Framework_TestCase
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
