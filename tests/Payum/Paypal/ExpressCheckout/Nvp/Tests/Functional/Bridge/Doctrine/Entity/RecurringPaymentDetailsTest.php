<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Functional\Bridge\Doctrine\Entity;

use Doctrine\ORM\Tools\SchemaValidator;

use Payum\Paypal\ExpressCheckout\Nvp\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Paypal\ExpressCheckout\Nvp\Examples\Entity\RecurringPaymentDetails;

class RecurringPaymentDetailsTest extends OrmTest
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
        $recurringPaymentDetails = new RecurringPaymentDetails;
        $recurringPaymentDetails->setToken('foo');
        $recurringPaymentDetails->setAmt(10);
        $recurringPaymentDetails->setDesc('aDesc');
        
        //guard
        $this->assertNull($recurringPaymentDetails->getId());
        
        $this->em->persist($recurringPaymentDetails);
        $this->em->flush();
        
        $this->assertGreaterThan(0, $recurringPaymentDetails->getId());
    }

    /**
     * @test
     */
    public function shouldAllowFindPersistedRecurringPaymentDetails()
    {
        $recurringPaymentDetails = new RecurringPaymentDetails;
        $recurringPaymentDetails->setToken($expectedToken = 'theToken');
        $recurringPaymentDetails->setAmt($expectedAmount = 10);
        $recurringPaymentDetails->setDesc($expectedDescription = 'aDesc');
        $recurringPaymentDetails->setProfileid($expectedProfileid = 123);
        
        $this->em->persist($recurringPaymentDetails);
        $this->em->flush();
        
        $id = $recurringPaymentDetails->getId();

        $this->em->clear();
        
        $foundInstruction = $this->em->find(get_class($recurringPaymentDetails), $id);
        
        $this->assertNotSame($recurringPaymentDetails, $foundInstruction);
        
        $this->assertEquals($expectedToken, $foundInstruction->getToken());
        $this->assertEquals($expectedProfileid, $foundInstruction->getProfileid());
        $this->assertEquals($expectedAmount, $foundInstruction->getAmt());
        $this->assertEquals($expectedDescription, $foundInstruction->getDesc());
    }

    /**
     * @test
     */
    public function shouldSaveOthersField()
    {
        $recurringPaymentDetails = new RecurringPaymentDetails;
        $recurringPaymentDetails->setToken('aToken');
        $recurringPaymentDetails->setAmt(10);
        $recurringPaymentDetails->setDesc('aDesc');
        $recurringPaymentDetails->setProfileid(123);
        $recurringPaymentDetails['foo'] = $expect = 'theFoo';

        $this->em->persist($recurringPaymentDetails);
        $this->em->flush();
        $this->em->clear();

        $foundDetails = $this->em->find(get_class($recurringPaymentDetails), $recurringPaymentDetails->getId());

        $this->assertEquals($expect, $foundDetails['foo']);
    }
}