<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Capture;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;

class CaptureTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(Capture::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
