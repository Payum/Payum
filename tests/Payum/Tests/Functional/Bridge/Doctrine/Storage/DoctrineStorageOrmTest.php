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
        
        $model = $storage->createModel();
        
        $storage->updateModel($model);
        
        $this->assertNotNull($model->getId());
    }

    /**
     * @test
     */
    public function shouldGetModelIdentifier()
    {
        $storage = new DoctrineStorage(
            $this->em,
            'Payum\Examples\Entity\TestModel'
        );

        $model = $storage->createModel();

        $storage->updateModel($model);

        $this->assertNotNull($model->getId());
        
        $identificator = $storage->getIdentificator($model);
        
        $this->assertInstanceOf('Payum\Storage\Identificator', $identificator);
        $this->assertEquals(get_class($model), $identificator->getClass());
        $this->assertEquals($model->getId(), $identificator->getId());
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

        $model = $storage->createModel();

        $storage->updateModel($model);
        
        $requestId = $model->getId();
        
        $this->em->clear();

        $model = $storage->findModelById($requestId);
        
        $this->assertInstanceOf('Payum\Examples\Model\TestModel', $model);
        $this->assertEquals($requestId, $model->getId());
    }
}
