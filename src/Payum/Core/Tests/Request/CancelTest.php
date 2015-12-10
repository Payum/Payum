<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Cancel;

class CancelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\Cancel');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModel()
    {
        new Cancel(new \stdClass());
    }
}
