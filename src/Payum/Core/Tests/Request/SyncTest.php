<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Generic;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;

class SyncTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(Sync::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
