<?php
namespace Payum\Tests\Functional\Bridge\Doctrine\Entity;

use Payum\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Examples\Entity\SimpleSell;

class SimpleSellTest extends OrmTest
{
    /**
     * @test
     */
    public function shouldAllowPersist()
    {
        $request = new SimpleSell;
        
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
        $request = new SimpleSell;
        $request->setCurrency($expectedCurrency = 'AUD');
        $request->setPrice($expectedPrice = 123.15);
        
        $this->em->persist($request);
        $this->em->flush();
        
        $id = $request->getId();

        $this->em->clear();
        
        $foundRequest = $this->em->find('Payum\Examples\Entity\SimpleSell', $id);
        
        $this->assertNotSame($request, $foundRequest);
        
        $this->assertEquals($expectedPrice, $foundRequest->getPrice());
        $this->assertEquals($expectedCurrency, $foundRequest->getCurrency());
    }
}