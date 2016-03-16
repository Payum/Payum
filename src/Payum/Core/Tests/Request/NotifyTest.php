<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Core\Request\Notify;

class NotifyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(Notify::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModel()
    {
        new Notify(new \stdClass());
    }
}
