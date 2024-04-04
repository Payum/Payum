<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Authorize;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;

class AuthorizeTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(Authorize::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
