<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Functional\Bridge\Doctrine\Document;

use Payum\Paypal\ExpressCheckout\Nvp\Examples\Document\RecurringPaymentDetails;
use Payum\Paypal\ExpressCheckout\Nvp\Tests\Functional\Bridge\Doctrine\MongoTest;

class RecurringPaymentDetailsTest extends MongoTest
{
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
        
        $this->dm->persist($recurringPaymentDetails);
        $this->dm->flush();
        
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
        $recurringPaymentDetails->setLErrorcoden(0, $expectedError = 'theError');
        
        $this->dm->persist($recurringPaymentDetails);
        $this->dm->flush();
        
        $id = $recurringPaymentDetails->getId();

        $this->dm->clear();
        
        $foundInstruction = $this->dm->find(get_class($recurringPaymentDetails), $id);
        
        $this->assertNotSame($recurringPaymentDetails, $foundInstruction);
        
        $this->assertEquals($expectedToken, $foundInstruction->getToken());
        $this->assertEquals($expectedProfileid, $foundInstruction->getProfileid());
        $this->assertEquals($expectedAmount, $foundInstruction->getAmt());
        $this->assertEquals($expectedDescription, $foundInstruction->getDesc());
        $this->assertEquals($expectedError, $foundInstruction->getLErrorcoden(0));
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

        $this->dm->persist($recurringPaymentDetails);
        $this->dm->flush();
        $this->dm->clear();

        $foundDetails = $this->dm->find(get_class($recurringPaymentDetails), $recurringPaymentDetails->getId());

        $this->assertEquals($expect, $foundDetails['foo']);
    }
}