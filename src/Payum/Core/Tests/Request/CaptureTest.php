<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Capture;

class CaptureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\Capture');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModel()
    {
        new Capture(new \stdClass());
    }
}
