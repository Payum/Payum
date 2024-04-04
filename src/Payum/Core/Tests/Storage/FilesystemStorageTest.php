<?php
namespace Payum\Core\Tests\Storage;

use Payum\Core\Storage\FilesystemStorage;
use PHPUnit\Framework\TestCase;

class FilesystemStorageTest extends TestCase
{
    public function testShouldBeSubClassOfAbstractStorage()
    {
        $rc = new \ReflectionClass('Payum\Core\Storage\FilesystemStorage');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Storage\AbstractStorage'));
    }

    public function testShouldCreateInstanceOfModelClassGivenInConstructor()
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

    public function testShouldUpdateModelAndSetIdToModel()
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

    public function testShouldUpdateModelAndSetIdToModelEvenIfModelNotHaveIdDefined()
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

    public function testShouldKeepIdTheSameOnSeveralUpdates()
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

    public function testShouldCreateFileWithModelInfoInStorageDirOnUpdateModel()
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

    public function testShouldGenerateDifferentIdsForDifferentModels()
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

    public function testThrowIfTryGetIdentifierOfNotPersistedModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The model must be persisted before usage of this method');
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

    public function testShouldAllowGetModelIdentity()
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

    public function testShouldAllowGetModelIdentityWhenDynamicIdUsed()
    {
        $storage = new FilesystemStorage(sys_get_temp_dir(), 'stdClass');

        $model = $storage->create();

        $storage->update($model);

        $identity = $storage->identify($model);

        $this->assertInstanceOf('Payum\Core\Model\Identity', $identity);
        $this->assertSame('stdClass', $identity->getClass());
        $this->assertEquals($model->payum_id, $identity->getId());
    }

    public function testThrowIfTryToUseNotSupportedFindByMethod()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('Method is not supported by the storage.');
        $storage = new FilesystemStorage(
            sys_get_temp_dir(),
            'Payum\Core\Tests\Mocks\Model\TestModel',
            'id'
        );

        $storage->findBy(array());
    }

    public function testShouldFindModelById()
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

    public function testShouldFindModelByIdentity()
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

    public function testShouldStoreInfoBetweenUpdateAndFind()
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
        $this->assertSame($expectedPrice, $foundModel->getPrice());
        $this->assertSame($expectedCurrency, $foundModel->getCurrency());
    }

    public function testShouldStoreInfoBetweenUpdateAndFindWithDefaultId()
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
        $this->assertSame($expectedPrice, $foundModel->getPrice());
        $this->assertSame($expectedCurrency, $foundModel->getCurrency());

        $this->assertObjectHasAttribute('payum_id', $foundModel);
        $this->assertNotEmpty($foundModel->payum_id);
    }

    public function testShouldAllowDeleteModel()
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
