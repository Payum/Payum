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
        $request = new PaymentDetails;
        
        //guard
        $this->assertNull($request->getId());
        
        $this->em->persist($request);
        $this->em->flush();
        
        $this->assertNotEmpty($request->getId());
    }

    /**
     * @test
     */
    public function shouldAllowFindPersistedRequest()
    {
        $instruction = new PaymentDetails;
        $instruction->setToken($expectedToken = 'theToken');
        $instruction->setPaymentrequestAmt(0, $expectedAmount = 123.15);
        $instruction->setPaymentrequestPaymentaction(0, $expectedAction = 'thePaymentAction');
        
        $this->em->persist($instruction);
        $this->em->flush();
        
        $id = $instruction->getId();

        $this->em->clear();
        
        $foundInstruction = $this->em->find(get_class($instruction), $id);
        
        $this->assertNotSame($instruction, $foundInstruction);
        
        $this->assertEquals($expectedToken, $foundInstruction->getToken());
        $this->assertEquals($expectedAmount, $foundInstruction->getPaymentrequestAmt(0));
        $this->assertEquals($expectedAction, $foundInstruction->getPaymentrequestPaymentaction(0));
    }
}