<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Cancel;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;

class CancelTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(Cancel::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
