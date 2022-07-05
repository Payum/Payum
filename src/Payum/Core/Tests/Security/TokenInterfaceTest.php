<?php

namespace Payum\Core\Tests\Security;

use PHPUnit\Framework\TestCase;

class TokenInterfaceTest extends TestCase
{
    public function testShouldExtendDetailsAwareInterface()
    {
        $rc = new \ReflectionClass(\Payum\Core\Security\TokenInterface::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\Model\DetailsAwareInterface::class));
    }

    public function testShouldExtendDetailsAggregateInterface()
    {
        $rc = new \ReflectionClass(\Payum\Core\Security\TokenInterface::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\Model\DetailsAggregateInterface::class));
    }
}
