<?php
namespace Payum\Tests\Functional\Bridge\Doctrine\Storage;

use Payum\Tests\Functional\Bridge\Doctrine\MongoTest;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;

class DoctrineStorageMongoOdmTest extends MongoTest
{
    /**
     * @test
     */
    public function shouldUpdateModelAndSetId()
    {
        $storage = new \Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage(
            $this->dm,
            'Payum\Examples\Document\TestModel'
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
            $this->dm,
            'Payum\Examples\Document\TestModel'
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
            $this->dm,
            'Payum\Examples\Document\TestModel'
        );

        $model = $storage->createModel();

        $storage->updateModel($model);
        
        $requestId = $model->getId();
        
        $this->dm->clear();

        $model = $storage->findModelById($requestId);
        
        $this->assertInstanceOf('Payum\Examples\Document\TestModel', $model);
        $this->assertEquals($requestId, $model->getId());
    }

    /**
     * @test
     */
    public function shouldFindModelByIdentificator()
    {
        $storage = new DoctrineStorage(
            $this->dm,
            'Payum\Examples\Document\TestModel'
        );

        $model = $storage->createModel();

        $storage->updateModel($model);

        $requestId = $model->getId();

        $this->dm->clear();

        $identificator = $storage->getIdentificator($model);

        $foundModel = $storage->findModelByIdentificator($identificator);

        $this->assertInstanceOf('Payum\Examples\Document\TestModel', $foundModel);
        $this->assertEquals($requestId, $foundModel->getId());
    }
}
