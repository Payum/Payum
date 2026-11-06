<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Exception;

use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\NotImplementedException;
use PHPUnit\Framework\TestCase;

final class NotImplementedExceptionTest extends TestCase
{
    public function testShouldBeSubClassOfLogicException(): void
    {
        $this->assertInstanceOf(LogicException::class, new NotImplementedException());
    }

    public function testShouldDefaultTheMessage(): void
    {
        $this->assertSame('Not implemented', (new NotImplementedException())->getMessage());
        $this->assertSame('Refunds are not implemented', (new NotImplementedException('Refunds are not implemented'))->getMessage());
    }
}
