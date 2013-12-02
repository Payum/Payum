<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Storage;

use Payum\Core\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;

class DoctrineStorageOrmTest extends OrmTest
{
    /**
     * @test
     */
    public function shouldUpdateModelAndSetId()
    {
        $storage = new DoctrineStorage(
            $this->em,
            'Payum\Core\Tests\Mocks\Entity\TestModel'
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
            'Payum\Core\Tests\Mocks\Entity\TestModel'
        );

        $model = $storage->createModel();

        $storage->updateModel($model);

        $this->assertNotNull($model->getId());
        
        $identificator = $storage->getIdentificator($model);
        
        $this->assertInstanceOf('Payum\Core\Model\Identificator', $identificator);
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
            'Payum\Core\Tests\Mocks\Entity\TestModel'
        );

        $model = $storage->createModel();

        $storage->updateModel($model);
        
        $requestId = $model->getId();
        
        $this->em->clear();

        $model = $storage->findModelById($requestId);
        
        $this->assertInstanceOf('Payum\Core\Tests\Mocks\Entity\TestModel', $model);
        $this->assertEquals($requestId, $model->getId());
    }

    /**
     * @test
     */
    public function shouldFindModelByIdentificator()
    {
        $storage = new DoctrineStorage(
            $this->em,
            'Payum\Core\Tests\Mocks\Entity\TestModel'
        );

        $model = $storage->createModel();

        $storage->updateModel($model);

        $requestId = $model->getId();

        $this->em->clear();

        $identificator = $storage->getIdentificator($model);

        $foundModel = $storage->findModelByIdentificator($identificator);

        $this->assertInstanceOf('Payum\Core\Tests\Mocks\Entity\TestModel', $foundModel);
        $this->assertEquals($requestId, $foundModel->getId());
    }
}
