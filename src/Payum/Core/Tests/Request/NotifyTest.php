<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Notify;

class NotifyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\Notify');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModel()
    {
        new Notify(new \stdClass());
    }
}
