<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Functional\Bridge\Doctrine\Entity;

use Doctrine\ORM\Tools\SchemaValidator;

use Payum\Paypal\ExpressCheckout\Nvp\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Paypal\ExpressCheckout\Nvp\Examples\Entity\PaymentDetails;

class PaymentDetailsTest extends OrmTest
{
    /**
     * @test
     */
    public function shouldAllSchemasBeValid()
    {
        $schemaValidator = new SchemaValidator($this->em);

        $this->assertEmpty($schemaValidator->validateMapping());
    }
    
    /**
     * @test
     */
    public function shouldAllowPersist()
    {
        $paymentDetails = new PaymentDetails;
        $paymentDetails->setToken('foo');
        
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
        $paymentDetails->setToken($expectedToken = 'theToken');
        $paymentDetails->setPaymentrequestAmt(0, $expectedAmount = 123.15);
        $paymentDetails->setPaymentrequestPaymentaction(0, $expectedAction = 'thePaymentAction');
        
        $this->em->persist($paymentDetails);
        $this->em->flush();
        
        $id = $paymentDetails->getId();

        $this->em->clear();
        
        $foundInstruction = $this->em->find(get_class($paymentDetails), $id);
        
        $this->assertNotSame($paymentDetails, $foundInstruction);
        
        $this->assertEquals($expectedToken, $foundInstruction->getToken());
        $this->assertEquals($expectedAmount, $foundInstruction->getPaymentrequestAmt(0));
        $this->assertEquals($expectedAction, $foundInstruction->getPaymentrequestPaymentaction(0));
    }

    /**
     * @test
     */
    public function shouldSaveOthersField()
    {
        $paymentDetails = new PaymentDetails;
        $paymentDetails['foo'] = $expect = 'theFoo';

        $this->em->persist($paymentDetails);
        $this->em->flush();
        $this->em->clear();

        $foundDetails = $this->em->find(get_class($paymentDetails), $paymentDetails->getId());

        $this->assertEquals($expect, $foundDetails['foo']);
    }
}