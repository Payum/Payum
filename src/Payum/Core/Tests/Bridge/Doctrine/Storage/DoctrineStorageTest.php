<?php
namespace Payum\Core\Tests\Bridge\Doctrine\Storage;

use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Tests\Mocks\Model\TestModel;

class DoctrineStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractStorage()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Storage\AbstractStorage'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithObjectManagerAndModelClassAsArguments()
    {
        new DoctrineStorage(
            $this->createObjectManagerMock(),
            'Payum\Core\Tests\Mocks\Model\TestModel'
        );
    }

    /**
     * @test
     */
    public function shouldCreateInstanceOfModelClassGivenInConstructor()
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

    /**
     * @test
     */
    public function shouldCallObjectManagerPersistAndFlushOnUpdateModel()
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

    /**
     * @test
     */
    public function shouldProxyCriteriaToRepositoryFindByMethodOnFindByCall()
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

    /**
     * @test
     */
    public function shouldFindModelById()
    {
        $expectedModelClass = 'Payum\Core\Tests\Mocks\Model\TestModel';
        $expectedModelId = 123;
        $expectedFoundModel = new TestModel();

        $objectManagerMock = $this->createObjectManagerMock();
        $objectManagerMock
            ->expects($this->once())
            ->method('find')
            ->with($expectedModelClass, $expectedModelId)
            ->will($this->returnValue($expectedFoundModel))
        ;

        $storage = new DoctrineStorage(
            $objectManagerMock,
            'Payum\Core\Tests\Mocks\Model\TestModel'
        );

        $actualModel = $storage->find($expectedModelId);

        $this->assertSame($expectedFoundModel, $actualModel);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Persistence\ObjectManager
     */
    protected function createObjectManagerMock()
    {
        return $this->getMock('Doctrine\Common\Persistence\ObjectManager');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Persistence\ObjectRepository
     */
    protected function createObjectRepositoryMock()
    {
        return $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
    }
}
