<?php

namespace Payum\Core\Tests\Storage;

use Payum\Core\Exception\LogicException;
use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Tests\Mocks\Model\TestModel;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class FilesystemStorageTest extends TestCase
{
    public function testShouldBeSubClassOfAbstractStorage(): void
    {
        $rc = new ReflectionClass(FilesystemStorage::class);

        $this->assertTrue($rc->isSubclassOf(AbstractStorage::class));
    }

    public function testShouldCreateInstanceOfModelClassGivenInConstructor(): void
    {
        $expectedModelClass = TestModel::class;

        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            $expectedModelClass,
            'id'
        );

        $model = $storage->create();

        $this->assertInstanceOf($expectedModelClass, $model);
        $this->assertNull($model->getId());
    }

    public function testShouldUpdateModelAndSetIdToModel(): void
    {
        $expectedModelClass = TestModel::class;

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

    public function testShouldUpdateModelAndSetIdToModelEvenIfModelNotHaveIdDefined(): void
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            stdClass::class,
            'notExistProperty'
        );

        $model = $storage->create();

        $storage->update($model);

        $this->assertInstanceOf(stdClass::class, $model);
        $this->assertObjectHasAttribute('notExistProperty', $model);
    }

    public function testShouldKeepIdTheSameOnSeveralUpdates(): void
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            TestModel::class,
            'id'
        );

        $model = $storage->create();

        $storage->update($model);
        $firstId = $model->getId();

        $storage->update($model);
        $secondId = $model->getId();

        $this->assertSame($firstId, $secondId);
    }

    public function testShouldCreateFileWithModelInfoInStorageDirOnUpdateModel(): void
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            TestModel::class,
            'id'
        );

        $model = $storage->create();
        $storage->update($model);

        $this->assertFileExists(sys_get_temp_dir() . '/payum-model-' . $model->getId());
    }

    public function testShouldGenerateDifferentIdsForDifferentModels(): void
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            TestModel::class,
            'id'
        );

        $modelOne = $storage->create();
        $storage->update($modelOne);

        $modelTwo = $storage->create();
        $storage->update($modelTwo);

        $this->assertNotSame($modelOne->getId(), $modelTwo->getId());
    }

    public function testThrowIfTryGetIdentifierOfNotPersistedModel(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The model must be persisted before usage of this method');
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            TestModel::class,
            'id'
        );

        $model = $storage->create();

        //guard
        $this->assertNull($model->getId());

        $storage->identify($model);
    }

    public function testShouldAllowGetModelIdentity(): void
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            TestModel::class,
            'id'
        );

        $model = $storage->create();

        $storage->update($model);
        $firstId = $model->getId();

        $storage->update($model);
        $secondId = $model->getId();

        $this->assertSame($firstId, $secondId);
    }

    public function testShouldAllowGetModelIdentityWhenDynamicIdUsed(): void
    {
        $storage = new FilesystemStorage(sys_get_temp_dir(), stdClass::class);

        $model = $storage->create();

        $storage->update($model);

        $identity = $storage->identify($model);

        $this->assertInstanceOf(Identity::class, $identity);
        $this->assertSame(stdClass::class, $identity->getClass());
        $this->assertEquals($model->payum_id, $identity->getId());
    }

    public function testThrowIfTryToUseNotSupportedFindByMethod(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Method is not supported by the storage.');
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            TestModel::class,
            'id'
        );

        $storage->findBy([]);
    }

    public function testShouldFindModelById(): void
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            TestModel::class,
            'id'
        );

        $model = $storage->create();
        $storage->update($model);

        //guard
        $this->assertNotEmpty($model->getId());

        $foundModel = $storage->find($model->getId());

        $this->assertInstanceOf(TestModel::class, $foundModel);
        $this->assertEquals($model->getId(), $foundModel->getId());
    }

    public function testShouldFindModelByIdentity(): void
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            TestModel::class,
            'id'
        );

        $model = $storage->create();
        $storage->update($model);

        //guard
        $this->assertNotEmpty($model->getId());

        $identity = $storage->identify($model);

        //guard
        $this->assertInstanceOf(Identity::class, $identity);

        $foundModel = $storage->find($identity);

        $this->assertInstanceOf(TestModel::class, $foundModel);
        $this->assertEquals($model->getId(), $foundModel->getId());
    }

    public function testShouldStoreInfoBetweenUpdateAndFind(): void
    {
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            TestModel::class,
            'id'
        );

        $model = $storage->create();
        $model->setPrice($expectedPrice = 123);
        $model->setCurrency($expectedCurrency = 'FOO');

        $storage->update($model);

        $foundModel = $storage->find($model->getId());

        $this->assertSame($model, $foundModel);
        $this->assertSame($expectedPrice, $foundModel->getPrice());
        $this->assertSame($expectedCurrency, $foundModel->getCurrency());
    }

    public function testShouldStoreInfoBetweenUpdateAndFindWithDefaultId(): void
    {
        $storage = new FilesystemStorage(sys_get_temp_dir(), TestModel::class);

        $model = $storage->create();
        $model->setPrice($expectedPrice = 123);
        $model->setCurrency($expectedCurrency = 'FOO');

        $storage->update($model);

        //guard
        $this->assertObjectHasAttribute('payum_id', $model);
        $this->assertNotEmpty($model->payum_id);

        $foundModel = $storage->find($model->payum_id);

        $this->assertSame($model, $foundModel);
        $this->assertSame($expectedPrice, $foundModel->getPrice());
        $this->assertSame($expectedCurrency, $foundModel->getCurrency());

        $this->assertObjectHasAttribute('payum_id', $foundModel);
        $this->assertNotEmpty($foundModel->payum_id);
    }

    public function testShouldAllowDeleteModel(): void
    {
        $storage = new FilesystemStorage(sys_get_temp_dir(), TestModel::class);

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
