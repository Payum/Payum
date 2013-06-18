<?php
namespace Payum\Payex\Tests\Functional\Bridge\Doctrine\Entity;

use Doctrine\ORM\Tools\SchemaValidator;

use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Payex\Examples\Entity\AgreementDetails;

class AgreementDetailsTest extends OrmTest
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
        $agreementDetails = new AgreementDetails;
        $agreementDetails->setMerchantRef('aRef');
        $agreementDetails->setDescription('aDesc');
        $agreementDetails->setPurchaseOperation(AgreementApi::PURCHASEOPERATION_SALE);
        $agreementDetails->setMaxAmount(900000);
        
        //guard
        $this->assertNull($agreementDetails->getId());

        $this->em->persist($agreementDetails);
        $this->em->flush();

        $this->assertGreaterThan(0, $agreementDetails->getId());
    }

    /**
     * @test
     */
    public function shouldAllowFindPersistedPaymentDetails()
    {
        $agreementDetails = new AgreementDetails;
        $agreementDetails->setMerchantRef($expectedMerchantRef = 'theRef');
        $agreementDetails->setDescription($expectedDescription = 'theDesc');
        $agreementDetails->setPurchaseOperation(AgreementApi::PURCHASEOPERATION_SALE);
        $agreementDetails->setMaxAmount($expectedMaxAmount = 900000);

        $this->em->persist($agreementDetails);
        $this->em->flush();

        $id = $agreementDetails->getId();

        $this->em->clear();

        $foundAgreementDetails = $this->em->find(get_class($agreementDetails), $id);

        $this->assertNotSame($agreementDetails, $foundAgreementDetails);

        $this->assertEquals($expectedMerchantRef, $foundAgreementDetails->getMerchantRef());
        $this->assertEquals($expectedDescription, $foundAgreementDetails->getDescription());
        $this->assertEquals($expectedMaxAmount, $foundAgreementDetails->getMaxAmount());
    }
}