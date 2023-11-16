<?php

namespace Payum\Core\Tests\Bridge\Doctrine\Storage;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Storage\AbstractStorage;
use Payum\Core\Tests\Mocks\Model\TestModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DoctrineStorageTest extends TestCase
{
    public function testShouldBeSubClassOfAbstractStorage(): void
    {
        $rc = new ReflectionClass(DoctrineStorage::class);

        $this->assertTrue($rc->isSubclassOf(AbstractStorage::class));
    }

    public function testShouldCreateInstanceOfModelClassGivenInConstructor(): void
    {
        $expectedModelClass = TestModel::class;

        $storage = new DoctrineStorage(
            $this->createObjectManagerMock(),
            $expectedModelClass
        );

        $model = $storage->create();

        $this->assertInstanceOf($expectedModelClass, $model);
        $this->assertNull($model->getId());
    }

    public function testShouldCallObjectManagerPersistAndFlushOnUpdateModel(): void
    {
        $objectManagerMock = $this->createObjectManagerMock();
        $objectManagerMock
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(TestModel::class))
        ;
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
        ;

        $storage = new DoctrineStorage(
            $objectManagerMock,
            TestModel::class
        );

        $model = $storage->create();

        $storage->update($model);
    }

    public function testShouldProxyCriteriaToRepositoryFindByMethodOnFindByCall(): void
    {
        $modelClass = TestModel::class;
        $model = new TestModel();

        $criteria = [
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ];

        $objectRepositoryMock = $this->createObjectRepositoryMock();
        $objectRepositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->with($criteria)
            ->willReturn($model)
        ;

        $objectManagerMock = $this->createObjectManagerMock();
        $objectManagerMock
            ->expects($this->once())
            ->method('getRepository')
            ->with($modelClass)
            ->willReturn($objectRepositoryMock)
        ;

        $storage = new DoctrineStorage(
            $objectManagerMock,
            TestModel::class
        );

        $this->assertSame($model, $storage->findBy($criteria));
    }

    public function testShouldFindModelById(): void
    {
        $expectedModelClass = TestModel::class;
        $expectedModelId = 123;
        $expectedFoundModel = new TestModel();

        $objectManagerMock = $this->createObjectManagerMock();
        $objectManagerMock
            ->expects($this->once())
            ->method('find')
            ->with($expectedModelClass, $expectedModelId)
            ->willReturn($expectedFoundModel)
        ;

        $storage = new DoctrineStorage(
            $objectManagerMock,
            TestModel::class
        );

        $actualModel = $storage->find($expectedModelId);

        $this->assertSame($expectedFoundModel, $actualModel);
    }

    /**
     * @return MockObject|ObjectManager
     */
    protected function createObjectManagerMock()
    {
        return $this->createMock(ObjectManager::class);
    }

    /**
     * @return MockObject|ObjectRepository
     */
    protected function createObjectRepositoryMock()
    {
        return $this->createMock(ObjectRepository::class);
    }
}
