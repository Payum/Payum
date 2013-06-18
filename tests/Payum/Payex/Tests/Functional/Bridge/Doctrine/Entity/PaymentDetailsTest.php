<?php
namespace Payum\Payex\Tests\Functional\Bridge\Doctrine\Entity;

use Doctrine\ORM\Tools\SchemaValidator;

use Payum\Payex\Api\OrderApi;
use Payum\Payex\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Payex\Examples\Entity\PaymentDetails;

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
        $paymentDetails->setPurchaseOperation(OrderApi::PURCHASEOPERATION_SALE);
        $paymentDetails->setPrice(1000);
        $paymentDetails->setCurrency('NOK');
        $paymentDetails->setVat(1000);
        $paymentDetails->setOrderId('anId');
        $paymentDetails->setProductNumber('aNum');
        $paymentDetails->setDescription('aDesc');
        
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
        $paymentDetails->setPurchaseOperation(OrderApi::PURCHASEOPERATION_SALE);
        $paymentDetails->setPrice($expectedPrice = 1000);
        $paymentDetails->setCurrency($expectedCurrency = 'NOK');
        $paymentDetails->setVat(1000);
        $paymentDetails->setOrderId($expectedOrderId = 'anId');
        $paymentDetails->setProductNumber($expectedProductNumber = 'aNum');
        $paymentDetails->setDescription($expectedDescription = 'aDesc');

        $this->em->persist($paymentDetails);
        $this->em->flush();

        $id = $paymentDetails->getId();

        $this->em->clear();

        $foundPaymentDetails = $this->em->find(get_class($paymentDetails), $id);

        $this->assertNotSame($paymentDetails, $foundPaymentDetails);

        $this->assertEquals($expectedPrice, $foundPaymentDetails->getPrice());
        $this->assertEquals($expectedCurrency, $foundPaymentDetails->getCurrency());
        $this->assertEquals($expectedOrderId, $foundPaymentDetails->getOrderId());
        $this->assertEquals($expectedProductNumber, $foundPaymentDetails->getProductNumber());
        $this->assertEquals($expectedDescription, $foundPaymentDetails->getDescription());
    }
}