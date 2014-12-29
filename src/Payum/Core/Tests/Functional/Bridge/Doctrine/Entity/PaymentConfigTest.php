<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Entity;

use Payum\Core\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Core\Tests\Mocks\Entity\PaymentConfig;

class PaymentConfigTest extends OrmTest
{
    /**
     * @test
     */
    public function shouldAllowPersistWithSomeFieldsSet()
    {
        $paymentConfig = new PaymentConfig();
        $paymentConfig->setPaymentName('fooPayment');
        $paymentConfig->setFactoryName('fooPaymentFactory');
        $paymentConfig->setConfig(array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ));

        $this->em->persist($paymentConfig);
        $this->em->flush();
    }

    /**
     * @test
     */
    public function shouldAllowFindPersistedPaymentConfig()
    {
        $paymentConfig = new PaymentConfig();
        $paymentConfig->setPaymentName('fooPayment');
        $paymentConfig->setFactoryName('fooPaymentFactory');
        $paymentConfig->setConfig(array());

        $this->em->persist($paymentConfig);
        $this->em->flush();

        $paymentName = $paymentConfig->getPaymentName();

        $this->em->clear();

        $foundPaymentConfig = $this->em->find(get_class($paymentConfig), $paymentName);

        //guard
        $this->assertNotSame($paymentConfig, $foundPaymentConfig);

        $this->assertEquals($paymentConfig->getPaymentName(), $foundPaymentConfig->getPaymentName());
    }
}
