<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Request;

use Payum\Core\Request\Authorize;
use Payum\Core\Request\Generic;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AuthorizeTest extends TestCase
{
    public function testShouldBeSubClassOfGeneric(): void
    {
        $rc = new ReflectionClass(Authorize::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
