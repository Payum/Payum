<?php
namespace Payum\Core\Tests\Security;

use PHPUnit\Framework\TestCase;

class TokenInterfaceTest extends TestCase
{
    /**
     * @test
     */
    public function shouldExtendDetailsAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Security\TokenInterface');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\DetailsAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldExtendDetailsAggregateInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Security\TokenInterface');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\DetailsAggregateInterface'));
    }
}
