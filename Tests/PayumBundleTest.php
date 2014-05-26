<?php
namespace Payum\Bundle\PayumBundle\Tests;

use Payum\Bundle\PayumBundle\PayumBundle;

class PayumBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBundle()
    {
        $rc = new \ReflectionClass('Payum\Bundle\PayumBundle\PayumBundle');

        $this->assertTrue($rc->isSubclassOf('Symfony\Component\HttpKernel\Bundle\Bundle'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PayumBundle;
    }
} 