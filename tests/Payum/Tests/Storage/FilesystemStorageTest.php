<?php
namespace Payum\Tests\Storage;

use Payum\Storage\FilesystemStorage;

class FilesystemStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractStorage()
    {
        $rc = new \ReflectionClass('Payum\Storage\FilesystemStorage');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Storage\AbstractStorage'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithStorageDirModelClassAndDefaultIdPropertyArguments()
    {
        $storage = new FilesystemStorage(sys_get_temp_dir(), 'Payum\Examples\Model\TestModel');

        $this->assertAttributeEquals('payum_id', 'idProperty', $storage);
    }

    /**
     * @test
     */
    public function couldBeConstructedWithStorageDirModelClassAndIdPropertyArguments()
    {
        new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Examples\Model\TestModel',
            'id'
        );
    }

    /**
     * @test
     */
    public function shouldCreateInstanceOfModelClassGivenInConstructor()
    {
        $expectedModelClass = 'Payum\Examples\Model\TestModel';
        
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            $expectedModelClass,
            'id'
        );
        
        $model = $storage->createModel();
        
        $this->assertInstanceOf($expectedModelClass, $model);
        $this->assertNull($model->getId());
    }

    /**
     * @test
     */
    public function shouldUpdateModelAndSetIdToModel()
    {
        $expectedModelClass = 'Payum\Examples\Model\TestModel';

        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            $expectedModelClass,
            'id'
        );

        $model = $storage->createModel();
        
        $storage->updateModel($model);

        $this->assertInstanceOf($expectedModelClass, $model);
        $this->assertNotEmpty($model->getId());
    }

    /**
     * @test
     */
    public function shouldUpdateModelAndSetIdToModelEvenIfModelNotHaveIdDefinied()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'stdClass',
            'notExistProperty'
        );

        $model = $storage->createModel();

        $storage->updateModel($model);

        $this->assertInstanceOf('stdClass', $model);
        $this->assertObjectHasAttribute('notExistProperty', $model);
    }

    /**
     * @test
     */
    public function shouldKeepIdTheSameOnSeveralUpdates()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Examples\Model\TestModel',
            'id'
        );

        $model = $storage->createModel();

        $storage->updateModel($model);
        $firstId = $model->getId();

        $storage->updateModel($model);
        $secondId = $model->getId();

        $this->assertSame($firstId, $secondId);
    }

    /**
     * @test
     */
    public function shouldCreateFileWithModelInfoInStorageDirOnUpdateModel()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Examples\Model\TestModel',
            'id'
        );

        $model = $storage->createModel();
        $storage->updateModel($model);
        
        $this->assertFileExists(sys_get_temp_dir().'/payum-model-'.$model->getId());
    }

    /**
     * @test
     */
    public function shouldGenerateDifferentIdsForDifferentModels()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Examples\Model\TestModel',
            'id'
        );

        $modelOne = $storage->createModel();
        $storage->updateModel($modelOne);

        $modelTwo = $storage->createModel();
        $storage->updateModel($modelTwo);

        $this->assertNotSame($modelOne->getId(), $modelTwo->getId());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The model must be persisted before usage of this method
     */
    public function throwIfTryGetIdentifierOfNotPersistedModel()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Examples\Model\TestModel',
            'id'
        );

        $model = $storage->createModel();

        //guard
        $this->assertNull($model->getId());

        $storage->getIdentificator($model);
    }

    /**
     * @test
     */
    public function shouldAllowGetModelIdentificator()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Examples\Model\TestModel',
            'id'
        );

        $model = $storage->createModel();

        $storage->updateModel($model);
        $firstId = $model->getId();

        $storage->updateModel($model);
        $secondId = $model->getId();

        $this->assertSame($firstId, $secondId);
    }

    /**
     * @test
     */
    public function shouldAllowGetModelIdentificatorWhenDynamicIdUsed()
    {
        $storage = new FilesystemStorage(sys_get_temp_dir(), 'stdClass');

        $model = $storage->createModel();

        $storage->updateModel($model);

        $identificator = $storage->getIdentificator($model);

        $this->assertInstanceOf('Payum\Model\Identificator', $identificator);
        $this->assertEquals('stdClass', $identificator->getClass());
        $this->assertEquals($model->payum_id, $identificator->getId());
    }

    /**
     * @test
     */
    public function shouldFindModelById()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Examples\Model\TestModel',
            'id'
        );
        
        $model = $storage->createModel();
        $storage->updateModel($model);

        //guard
        $this->assertNotEmpty($model->getId());
        
        $foundModel = $storage->findModelById($model->getId());

        $this->assertInstanceOf('Payum\Examples\Model\TestModel', $foundModel);
        $this->assertEquals($model->getId(), $foundModel->getId());
    }

    /**
     * @test
     */
    public function shouldFindModelByIdentificator()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Examples\Model\TestModel',
            'id'
        );

        $model = $storage->createModel();
        $storage->updateModel($model);

        //guard
        $this->assertNotEmpty($model->getId());

        $identificator = $storage->getIdentificator($model);

        //guard
        $this->assertInstanceOf('Payum\Model\Identificator', $identificator);


        $foundModel = $storage->findModelByIdentificator($identificator);

        $this->assertInstanceOf('Payum\Examples\Model\TestModel', $foundModel);
        $this->assertEquals($model->getId(), $foundModel->getId());
    }

    /**
     * @test
     */
    public function shouldStoreInfoBetweenUpdateAndFind()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Examples\Model\TestModel',
            'id'
        );

        $model = $storage->createModel();
        $model->setPrice($expectedPrice = 123);
        $model->setCurrency($expectedCurrency = 'FOO');
        
        $storage->updateModel($model);

        $foundModel = $storage->findModelById($model->getId());

        $this->assertNotSame($model, $foundModel);
        $this->assertEquals($expectedPrice, $foundModel->getPrice());
        $this->assertEquals($expectedCurrency, $foundModel->getCurrency());
    }

    /**
     * @test
     */
    public function shouldStoreInfoBetweenUpdateAndFindWithDefaultId()
    {
        $storage = new FilesystemStorage(sys_get_temp_dir(), 'Payum\Examples\Model\TestModel');

        $model = $storage->createModel();
        $model->setPrice($expectedPrice = 123);
        $model->setCurrency($expectedCurrency = 'FOO');

        $storage->updateModel($model);

        //guard
        $this->assertObjectHasAttribute('payum_id', $model);
        $this->assertNotEmpty($model->payum_id);

        $foundModel = $storage->findModelById($model->payum_id);

        $this->assertNotSame($model, $foundModel);
        $this->assertEquals($expectedPrice, $foundModel->getPrice());
        $this->assertEquals($expectedCurrency, $foundModel->getCurrency());

        $this->assertObjectHasAttribute('payum_id', $foundModel);
        $this->assertNotEmpty($foundModel->payum_id);
    }
}
