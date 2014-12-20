<?php
namespace Payum\Core\Tests\Security;

class TokenInterfaceTest extends \PHPUnit_Framework_TestCase
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
