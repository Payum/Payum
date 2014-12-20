<?php
namespace Payum\Core\Tests\Request;

class SyncTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\Sync');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
