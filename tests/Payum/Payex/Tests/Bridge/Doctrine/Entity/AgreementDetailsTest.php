<?php
namespace Payum\Payex\Tests\Bridge\Doctrine\Entity;

class AgreementDetailsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfInstruction()
    {
        $rc = new \ReflectionClass('Payum\Payex\Bridge\Doctrine\Entity\AgreementDetails');

        $this->assertTrue($rc->isSubclassOf('Payum\Payex\Model\AgreementDetails'));
    }
}