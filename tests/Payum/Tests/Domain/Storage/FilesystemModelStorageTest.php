<?php
namespace Payum\Tests\Domain\Storage;

use Payum\Domain\Storage\FilesystemModelStorage;
use Payum\Domain\SimpleSell;

class FilesystemModelStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementModelStorageInterface()
    {
        $rc = new \ReflectionClass('Payum\Domain\Storage\FilesystemModelStorage');
        
        $this->assertTrue($rc->implementsInterface('Payum\Domain\Storage\ModelStorageInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithStorageDirModelClassAndIdPropertyArguments()
    {
        new FilesystemModelStorage(
            sys_get_temp_dir(), 
            'Payum\Domain\SimpleSell',
            'id'
        );
    }

    /**
     * @test
     */
    public function shouldCreateInstanceOfModelClassGivenInConstructor()
    {
        $expectedModelClass = 'Payum\Domain\SimpleSell';
        
        $storage = new FilesystemModelStorage(
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
        $expectedModelClass = 'Payum\Domain\SimpleSell';

        $storage = new FilesystemModelStorage(
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
        $storage = new FilesystemModelStorage(
            sys_get_temp_dir(),
            'Payum\Domain\SimpleSell',
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
        $storage = new FilesystemModelStorage(
            sys_get_temp_dir(),
            'Payum\Domain\SimpleSell',
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
        $storage = new FilesystemModelStorage(
            sys_get_temp_dir(),
            'Payum\Domain\SimpleSell',
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
     * @expectedExceptionMessage Invalid model given. Should be instance of Payum\Domain\SimpleSell
     */
    public function throwIfTryUpdateModelNotInstanceOfModelClass()
    {
        $storage = new FilesystemModelStorage(
            sys_get_temp_dir(),
            'Payum\Domain\SimpleSell',
            'id'
        );
        
        $storage->updateModel($this->getMock('Payum\Domain\ModelInterface'));
    }

    /**
     * @test
     */
    public function shouldFindModelById()
    {
        $storage = new FilesystemModelStorage(
            sys_get_temp_dir(),
            'Payum\Domain\SimpleSell',
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
        $storage = new FilesystemModelStorage(
            sys_get_temp_dir(),
            'Payum\Domain\SimpleSell',
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