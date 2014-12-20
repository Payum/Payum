<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Storage;

use Payum\Core\Tests\Functional\Bridge\Doctrine\MongoTest;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;

class DoctrineStorageMongoOdmTest extends MongoTest
{
    /**
     * @test
     */
    public function shouldUpdateModelAndSetId()
    {
        $storage = new DoctrineStorage(
            $this->dm,
            'Payum\Core\Tests\Mocks\Document\TestModel'
        );

        $model = $storage->create();

        $storage->update($model);

        $this->assertNotNull($model->getId());
    }

    /**
     * @test
     */
    public function shouldGetModelIdentifier()
    {
        $storage = new DoctrineStorage(
            $this->dm,
            'Payum\Core\Tests\Mocks\Document\TestModel'
        );

        $model = $storage->create();

        $storage->update($model);

        $this->assertNotNull($model->getId());

        $identity = $storage->identify($model);

        $this->assertInstanceOf('Payum\Core\Model\Identity', $identity);
        $this->assertEquals(get_class($model), $identity->getClass());
        $this->assertEquals($model->getId(), $identity->getId());
    }

    /**
     * @test
     */
    public function shouldFindModelById()
    {
        $storage = new DoctrineStorage(
            $this->dm,
            'Payum\Core\Tests\Mocks\Document\TestModel'
        );

        $model = $storage->create();

        $storage->update($model);

        $requestId = $model->getId();

        $this->dm->clear();

        $model = $storage->find($requestId);

        $this->assertInstanceOf('Payum\Core\Tests\Mocks\Document\TestModel', $model);
        $this->assertEquals($requestId, $model->getId());
    }

    /**
     * @test
     */
    public function shouldFindModelByIdentity()
    {
        $storage = new DoctrineStorage(
            $this->dm,
            'Payum\Core\Tests\Mocks\Document\TestModel'
        );

        $model = $storage->create();

        $storage->update($model);

        $requestId = $model->getId();

        $this->dm->clear();

        $identity = $storage->identify($model);

        $foundModel = $storage->find($identity);

        $this->assertInstanceOf('Payum\Core\Tests\Mocks\Document\TestModel', $foundModel);
        $this->assertEquals($requestId, $foundModel->getId());
    }
}
