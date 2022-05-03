<?php
namespace Payum\Core\Tests\Bridge\Doctrine\Storage;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Tests\Mocks\Model\TestModel;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class DoctrineStorageTest extends TestCase
{
    public function testShouldBeSubClassOfAbstractStorage(): void
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Storage\AbstractStorage'));
    }

    public function testShouldCreateInstanceOfModelClassGivenInConstructor(): void
    {
        $expectedModelClass = 'Payum\Core\Tests\Mocks\Model\TestModel';

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
            ->with($this->isInstanceOf('Payum\Core\Tests\Mocks\Model\TestModel'))
        ;
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
        ;

        $storage = new DoctrineStorage(
            $objectManagerMock,
            'Payum\Core\Tests\Mocks\Model\TestModel'
        );

        $model = $storage->create();

        $storage->update($model);
    }

    public function testShouldProxyCriteriaToRepositoryFindByMethodOnFindByCall(): void
    {
        $modelClass = 'Payum\Core\Tests\Mocks\Model\TestModel';
        $model = new TestModel();

        $criteria = array('foo' => 'fooVal', 'bar' => 'barVal');

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
            'Payum\Core\Tests\Mocks\Model\TestModel'
        );

        $this->assertSame($model, $storage->findBy($criteria));
    }

    public function testShouldFindModelById(): void
    {
        $expectedModelClass = 'Payum\Core\Tests\Mocks\Model\TestModel';
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
            'Payum\Core\Tests\Mocks\Model\TestModel'
        );

        $actualModel = $storage->find($expectedModelId);

        $this->assertSame($expectedFoundModel, $actualModel);
    }

    protected function createObjectManagerMock(): MockObject|ObjectRepository
    {
        return $this->createMock(ObjectManager::class);
    }

    protected function createObjectRepositoryMock(): MockObject|ObjectRepository
    {
        return $this->createMock(ObjectRepository::class);
    }
}
