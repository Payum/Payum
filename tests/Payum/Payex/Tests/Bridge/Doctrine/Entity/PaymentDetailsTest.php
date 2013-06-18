<?php
namespace Payum\Payex\Tests\Bridge\Doctrine\Entity;

class PaymentDetailsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfInstruction()
    {
        $rc = new \ReflectionClass('Payum\Payex\Bridge\Doctrine\Entity\PaymentDetails');

        $this->assertTrue($rc->isSubclassOf('Payum\Payex\Model\PaymentDetails'));
    }
}