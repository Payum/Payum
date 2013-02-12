<?php
namespace Payum\Tests\Storage;

use \Payum\Storage\FilesystemStorage;

class FilesystemStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementStorageInterface()
    {
        $rc = new \ReflectionClass('Payum\Storage\FilesystemStorage');
        
        $this->assertTrue($rc->implementsInterface('Payum\Storage\StorageInterface'));
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
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid model given. Should be instance of Payum\Examples\Model\TestModel
     */
    public function throwIfTryUpdateModelNotInstanceOfModelClass()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Examples\Model\TestModel',
            'id'
        );
        
        $storage->updateModel(new \stdClass);
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

        $foundModel = $storage->findModelById($model->getId());
        
        $this->assertNotSame($model, $foundModel);
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
}