<?php
namespace Payum\Tests\Functional\Bridge\Doctrine\Request\Storage;

use Payum\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Bridge\Doctrine\Storage\DoctrineModelStorage;

class DoctrineModelStorageOrmTest extends OrmTest
{
    /**
     * @test
     */
    public function shouldUpdateModelAndSetId()
    {
        $storage = new DoctrineModelStorage(
            $this->em,
            'Payum\Examples\Entity\SimpleSell'
        );
        
        $request = $storage->createModel();
        
        $storage->updateModel($request);
        
        $this->assertNotNull($request->getId());
    }

    /**
     * @test
     */
    public function shouldFindModelById()
    {
        $storage = new DoctrineModelStorage(
            $this->em,
            'Payum\Examples\Entity\SimpleSell'
        );

        $request = $storage->createModel();

        $storage->updateModel($request);
        
        $requestId = $request->getId();
        
        $this->em->clear();

        $request = $storage->findModelById($requestId);
        
        $this->assertInstanceOf('Payum\Examples\Entity\SimpleSell', $request);
        $this->assertEquals($requestId, $request->getId());
    }
}
