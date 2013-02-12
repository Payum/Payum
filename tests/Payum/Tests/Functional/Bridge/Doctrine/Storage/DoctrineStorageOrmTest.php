<?php
namespace Payum\Tests\Functional\Bridge\Doctrine\Request\Storage;

use Payum\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Bridge\Doctrine\Storage\DoctrineStorage;

class DoctrineStorageOrmTest extends OrmTest
{
    /**
     * @test
     */
    public function shouldUpdateModelAndSetId()
    {
        $storage = new DoctrineStorage(
            $this->em,
            'Payum\Examples\Entity\TestModel'
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
        $storage = new DoctrineStorage(
            $this->em,
            'Payum\Examples\Entity\TestModel'
        );

        $request = $storage->createModel();

        $storage->updateModel($request);
        
        $requestId = $request->getId();
        
        $this->em->clear();

        $request = $storage->findModelById($requestId);
        
        $this->assertInstanceOf('Payum\Examples\Model\TestModel', $request);
        $this->assertEquals($requestId, $request->getId());
    }
}
