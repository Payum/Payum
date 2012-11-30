<?php
namespace Payum\Tests\Functional\Bridge\Doctrine\Request\Storage;

use Payum\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Bridge\Doctrine\Request\Storage\DoctrineRequestStorage;

class DoctrineRequestStorageOrmTest extends OrmTest
{
    /**
     * @test
     */
    public function shouldUpdateRequestAndSetId()
    {
        $storage = new DoctrineRequestStorage(
            $this->em,
            'Payum\Examples\Entity\SimpleSellRequest'
        );
        
        $request = $storage->createRequest();
        
        $storage->updateRequest($request);
        
        $this->assertNotNull($request->getId());
    }

    /**
     * @test
     */
    public function shouldFindRequestById()
    {
        $storage = new DoctrineRequestStorage(
            $this->em,
            'Payum\Examples\Entity\SimpleSellRequest'
        );

        $request = $storage->createRequest();

        $storage->updateRequest($request);
        
        $requestId = $request->getId();
        
        $this->em->clear();

        $request = $storage->findRequestById($requestId);
        
        $this->assertInstanceOf('Payum\Examples\Entity\SimpleSellRequest', $request);
        $this->assertEquals($requestId, $request->getId());
    }
}
