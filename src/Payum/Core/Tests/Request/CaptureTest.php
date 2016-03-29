<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;

class CaptureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(Capture::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModel()
    {
        new Capture(new \stdClass());
    }
}
