<?php
namespace Payum\Tests\Security;

class TokenInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldExtendDetailsAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Security\TokenInterface');

        $this->assertTrue($rc->implementsInterface('Payum\Model\DetailsAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldExtendDetailsAggregateInterface()
    {
        $rc = new \ReflectionClass('Payum\Security\TokenInterface');

        $this->assertTrue($rc->implementsInterface('Payum\Model\DetailsAggregateInterface'));
    }
}