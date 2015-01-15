<?php
namespace Payum\Core\Tests\Storage;

use Payum\Core\Storage\FilesystemStorage;

class FilesystemStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractStorage()
    {
        $rc = new \ReflectionClass('Payum\Core\Storage\FilesystemStorage');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Storage\AbstractStorage'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithStorageDirModelClassAndDefaultIdPropertyArguments()
    {
        $storage = new FilesystemStorage(sys_get_temp_dir(), 'Payum\Core\Tests\Mocks\Model\TestModel');

        $this->assertAttributeEquals('payum_id', 'idProperty', $storage);
    }

    /**
     * @test
     */
    public function couldBeConstructedWithStorageDirModelClassAndIdPropertyArguments()
    {
        new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Core\Tests\Mocks\Model\TestModel',
            'id'
        );
    }

    /**
     * @test
     */
    public function shouldCreateInstanceOfModelClassGivenInConstructor()
    {
        $expectedModelClass = 'Payum\Core\Tests\Mocks\Model\TestModel';

        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            $expectedModelClass,
            'id'
        );

        $model = $storage->create();

        $this->assertInstanceOf($expectedModelClass, $model);
        $this->assertNull($model->getId());
    }

    /**
     * @test
     */
    public function shouldUpdateModelAndSetIdToModel()
    {
        $expectedModelClass = 'Payum\Core\Tests\Mocks\Model\TestModel';

        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            $expectedModelClass,
            'id'
        );

        $model = $storage->create();

        $storage->update($model);

        $this->assertInstanceOf($expectedModelClass, $model);
        $this->assertNotEmpty($model->getId());
    }

    /**
     * @test
     */
    public function shouldUpdateModelAndSetIdToModelEvenIfModelNotHaveIdDefined()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'stdClass',
            'notExistProperty'
        );

        $model = $storage->create();

        $storage->update($model);

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
            'Payum\Core\Tests\Mocks\Model\TestModel',
            'id'
        );

        $model = $storage->create();

        $storage->update($model);
        $firstId = $model->getId();

        $storage->update($model);
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
            'Payum\Core\Tests\Mocks\Model\TestModel',
            'id'
        );

        $model = $storage->create();
        $storage->update($model);

        $this->assertFileExists(sys_get_temp_dir().'/payum-model-'.$model->getId());
    }

    /**
     * @test
     */
    public function shouldGenerateDifferentIdsForDifferentModels()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Core\Tests\Mocks\Model\TestModel',
            'id'
        );

        $modelOne = $storage->create();
        $storage->update($modelOne);

        $modelTwo = $storage->create();
        $storage->update($modelTwo);

        $this->assertNotSame($modelOne->getId(), $modelTwo->getId());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The model must be persisted before usage of this method
     */
    public function throwIfTryGetIdentifierOfNotPersistedModel()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Core\Tests\Mocks\Model\TestModel',
            'id'
        );

        $model = $storage->create();

        //guard
        $this->assertNull($model->getId());

        $storage->identify($model);
    }

    /**
     * @test
     */
    public function shouldAllowGetModelIdentity()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Core\Tests\Mocks\Model\TestModel',
            'id'
        );

        $model = $storage->create();

        $storage->update($model);
        $firstId = $model->getId();

        $storage->update($model);
        $secondId = $model->getId();

        $this->assertSame($firstId, $secondId);
    }

    /**
     * @test
     */
    public function shouldAllowGetModelIdentityWhenDynamicIdUsed()
    {
        $storage = new FilesystemStorage(sys_get_temp_dir(), 'stdClass');

        $model = $storage->create();

        $storage->update($model);

        $identity = $storage->identify($model);

        $this->assertInstanceOf('Payum\Core\Model\Identity', $identity);
        $this->assertEquals('stdClass', $identity->getClass());
        $this->assertEquals($model->payum_id, $identity->getId());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Method is not supported by the storage.
     */
    public function throwIfTryToUseNotSupportedFindByMethod()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Core\Tests\Mocks\Model\TestModel',
            'id'
        );

        $storage->findBy(array());
    }

    /**
     * @test
     */
    public function shouldFindModelById()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Core\Tests\Mocks\Model\TestModel',
            'id'
        );

        $model = $storage->create();
        $storage->update($model);

        //guard
        $this->assertNotEmpty($model->getId());

        $foundModel = $storage->find($model->getId());

        $this->assertInstanceOf('Payum\Core\Tests\Mocks\Model\TestModel', $foundModel);
        $this->assertEquals($model->getId(), $foundModel->getId());
    }

    /**
     * @test
     */
    public function shouldFindModelByIdentity()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Core\Tests\Mocks\Model\TestModel',
            'id'
        );

        $model = $storage->create();
        $storage->update($model);

        //guard
        $this->assertNotEmpty($model->getId());

        $identity = $storage->identify($model);

        //guard
        $this->assertInstanceOf('Payum\Core\Model\Identity', $identity);

        $foundModel = $storage->find($identity);

        $this->assertInstanceOf('Payum\Core\Tests\Mocks\Model\TestModel', $foundModel);
        $this->assertEquals($model->getId(), $foundModel->getId());
    }

    /**
     * @test
     */
    public function shouldStoreInfoBetweenUpdateAndFind()
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Core\Tests\Mocks\Model\TestModel',
            'id'
        );

        $model = $storage->create();
        $model->setPrice($expectedPrice = 123);
        $model->setCurrency($expectedCurrency = 'FOO');

        $storage->update($model);

        $foundModel = $storage->find($model->getId());

        $this->assertSame($model, $foundModel);
        $this->assertEquals($expectedPrice, $foundModel->getPrice());
        $this->assertEquals($expectedCurrency, $foundModel->getCurrency());
    }

    /**
     * @test
     */
    public function shouldStoreInfoBetweenUpdateAndFindWithDefaultId()
    {
        $storage = new FilesystemStorage(sys_get_temp_dir(), 'Payum\Core\Tests\Mocks\Model\TestModel');

        $model = $storage->create();
        $model->setPrice($expectedPrice = 123);
        $model->setCurrency($expectedCurrency = 'FOO');

        $storage->update($model);

        //guard
        $this->assertObjectHasAttribute('payum_id', $model);
        $this->assertNotEmpty($model->payum_id);

        $foundModel = $storage->find($model->payum_id);

        $this->assertSame($model, $foundModel);
        $this->assertEquals($expectedPrice, $foundModel->getPrice());
        $this->assertEquals($expectedCurrency, $foundModel->getCurrency());

        $this->assertObjectHasAttribute('payum_id', $foundModel);
        $this->assertNotEmpty($foundModel->payum_id);
    }

    /**
     * @test
     */
    public function shouldAllowDeleteModel()
    {
        $storage = new FilesystemStorage(sys_get_temp_dir(), 'Payum\Core\Tests\Mocks\Model\TestModel');

        $model = $storage->create();
        $model->setPrice($expectedPrice = 123);
        $model->setCurrency($expectedCurrency = 'FOO');

        $storage->update($model);

        //guard
        $this->assertObjectHasAttribute('payum_id', $model);
        $this->assertNotEmpty($model->payum_id);

        $storage->delete($model);

        $this->assertNull($storage->find($model->payum_id));
    }
}
