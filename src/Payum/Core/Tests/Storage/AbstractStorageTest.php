<?php

namespace Payum\Core\Tests\Storage;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class AbstractStorageTest extends TestCase
{
    public function testShouldImplementStorageInterface()
    {
        $rc = new ReflectionClass(AbstractStorage::class);

        $this->assertTrue($rc->implementsInterface(StorageInterface::class));
    }

    public function testShouldBeAbstract()
    {
        $rc = new ReflectionClass(AbstractStorage::class);

        $this->assertTrue($rc->isAbstract());
    }

    public function testShouldCreateInstanceOfModelClassSetInConstructor()
    {
        $storage = new class(stdClass::class) extends AbstractStorage {
            protected function doUpdateModel($model)
            {
            }

            protected function doDeleteModel($model)
            {
            }

            protected function doGetIdentity($model)
            {
            }

            protected function doFind($id)
            {
            }

            public function findBy(array $criteria)
            {
            }
        };

        $model = $storage->create();

        $this->assertInstanceOf(stdClass::class, $model);
    }

    public function testThrowIfInvalidModelGivenOnUpdate()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid model given. Should be instance of Mock_stdClass_');
        $modelClass = $this->createMock(stdClass::class)::class;

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [$modelClass]);

        $storage->update(new stdClass());
    }

    public function testShouldCallDoUpdateModelOnModelUpdate()
    {
        $model = new stdClass();

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [stdClass::class]);
        $storage
            ->expects($this->once())
            ->method('doUpdateModel')
            ->with($this->identicalTo($model))
        ;

        $storage->update($model);
    }

    public function testThrowIfInvalidModelGivenOnDelete()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid model given. Should be instance of Mock_stdClass_');
        $modelClass = $this->createMock(stdClass::class)::class;

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [$modelClass]);

        $storage->delete(new stdClass());
    }

    public function testShouldCallDoDeleteModelOnModelDelete()
    {
        $model = new stdClass();

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [stdClass::class]);
        $storage
            ->expects($this->once())
            ->method('doDeleteModel')
            ->with($this->identicalTo($model))
        ;

        $storage->delete($model);
    }

    public function testThrowIfInvalidModelGivenOnGetIdentity()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid model given. Should be instance of Mock_stdClass_');
        $modelClass = $this->createMock(stdClass::class)::class;

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [$modelClass]);

        $storage->identify(new stdClass());
    }

    public function testShouldCallDoGetIdentityOnGetIdentificator()
    {
        $model = new stdClass();

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [stdClass::class]);
        $storage
            ->expects($this->once())
            ->method('doGetIdentity')
            ->with($this->identicalTo($model))
        ;

        $storage->identify($model);
    }

    public function testShouldReturnNullIfNotSupportedIdentityGivenOnFindModelByIdentity()
    {
        $modelClass = $this->createMock(stdClass::class)::class;
        $identity = new Identity('anId', new stdClass());

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [$modelClass]);

        $this->assertNull($storage->find($identity));
    }

    public function testShouldCallFindModelByIdOnFindModelByIdentityWithIdFromIdentity()
    {
        $identity = new Identity('theId', new stdClass());

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [stdClass::class]);
        $storage
            ->expects($this->once())
            ->method('doFind')
            ->with('theId')
        ;

        $storage->find($identity);
    }

    public function testShouldCallFindModelByIdOnFindModelByEvenIfModelClassPrependWithSlash()
    {
        $identity = new Identity('theId', new stdClass());

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [stdClass::class]);
        $storage
            ->expects($this->once())
            ->method('doFind')
            ->with('theId')
        ;

        $storage->find($identity);
    }

    public function testShouldProxyFindModelByIdResultOnFindModelByIdentity()
    {
        $expectedModel = new stdClass();
        $identity = new Identity('aId', $expectedModel);

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [stdClass::class]);
        $storage
            ->expects($this->once())
            ->method('doFind')
            ->willReturn($expectedModel)
        ;

        $this->assertSame($expectedModel, $storage->find($identity));
    }

    public function testShouldNotCallDoFindIfIdentityClassNotMatchStorageOne()
    {
        $expectedModel = new stdClass();
        $identity = new Identity('aId', $expectedModel);

        $modelClass = $this->createMock(stdClass::class)::class;

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [$modelClass]);
        $storage
            ->expects($this->never())
            ->method('doFind')
        ;

        $this->assertNull($storage->find($identity));
    }

    public function testShouldReturnTrueIfModelSupportedOnSupportModel()
    {
        $model = $this->createMock(stdClass::class);

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [$model::class]);

        $this->assertTrue($storage->support($model));
    }

    public function testShouldReturnFalseIfModelNotSupportedOnSupportModel()
    {
        $modelClass = $this->createMock(stdClass::class)::class;

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [$modelClass]);

        $this->assertFalse($storage->support(new stdClass()));
    }

    public function testShouldReturnFalseIfModelNotObjectOnSupportModel()
    {
        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [stdClass::class]);

        $this->assertFalse($storage->support('notObject'));
    }
}
