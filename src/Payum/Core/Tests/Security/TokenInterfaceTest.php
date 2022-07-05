<?php

namespace Payum\Core\Tests\Security;

use Payum\Core\Model\DetailsAggregateInterface;
use Payum\Core\Model\DetailsAwareInterface;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class TokenInterfaceTest extends TestCase
{
    public function testShouldExtendDetailsAwareInterface()
    {
        $rc = new ReflectionClass(TokenInterface::class);

        $this->assertTrue($rc->implementsInterface(DetailsAwareInterface::class));
    }

    public function testShouldExtendDetailsAggregateInterface()
    {
        $rc = new ReflectionClass(TokenInterface::class);

        $this->assertTrue($rc->implementsInterface(DetailsAggregateInterface::class));
    }
}
