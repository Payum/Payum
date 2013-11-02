<?php
namespace Payum\Be2Bill\Tests\Functional\Bridge\Doctrine\Entity;

use Payum\Be2Bill\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Be2Bill\Examples\Entity\PaymentDetails;

class PaymentDetailsTest extends OrmTest
{
    /**
     * @test
     */
    public function shouldAllowPersist()
    {
        $paymentDetails = new PaymentDetails;
        
        //guard
        $this->assertNull($paymentDetails->getId());
        
        $this->em->persist($paymentDetails);
        $this->em->flush();
        
        $this->assertGreaterThan(0, $paymentDetails->getId());
    }

    /**
     * @test
     */
    public function shouldAllowFindPersistedPaymentDetails()
    {
        $paymentDetails = new PaymentDetails;
        $paymentDetails->setAmount($expectedAmount = 123);
        
        $this->em->persist($paymentDetails);
        $this->em->flush();
        
        $id = $paymentDetails->getId();

        $this->em->clear();
        
        $foundInstruction = $this->em->find(get_class($paymentDetails), $id);
        
        $this->assertNotSame($paymentDetails, $foundInstruction);
        
        $this->assertEquals($expectedAmount, $foundInstruction->getAmount());
    }
}