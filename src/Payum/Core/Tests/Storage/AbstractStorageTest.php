<?php

namespace Payum\Core\Tests\Storage;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;
use Payum\Core\Storage\IdentityInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class AbstractStorageTest extends TestCase
{
    public function testShouldImplementStorageInterface(): void
    {
        $rc = new ReflectionClass(AbstractStorage::class);

        $this->assertTrue($rc->implementsInterface(StorageInterface::class));
    }

    public function testShouldBeAbstract(): void
    {
        $rc = new ReflectionClass(AbstractStorage::class);

        $this->assertTrue($rc->isAbstract());
    }

    public function testShouldCreateInstanceOfModelClassSetInConstructor(): void
    {
        $storage = new class(stdClass::class) extends AbstractStorage {
            protected function doUpdateModel(object $model): object
            {
                return $model;
            }

            protected function doDeleteModel(object $model): void
            {
            }

            protected function doGetIdentity(object $model): IdentityInterface
            {
                return new Identity(1, $this->modelClass);
            }

            protected function doFind(mixed $id): ?object
            {
                return null;
            }

            /**
             * @param array<string, mixed> $criteria
             * @return mixed[]
             */
            public function findBy(array $criteria): array
            {
                return [];
            }
        };

        $model = $storage->create();

        $this->assertInstanceOf(stdClass::class, $model);
    }

    public function testThrowIfInvalidModelGivenOnUpdate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid model given. Should be instance of Mock_stdClass_');
        $modelClass = get_class($this->createMock(stdClass::class));

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [$modelClass]);

        $storage->update(new stdClass());
    }

    public function testShouldCallDoUpdateModelOnModelUpdate(): void
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

    public function testThrowIfInvalidModelGivenOnDelete(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid model given. Should be instance of Mock_stdClass_');
        $modelClass = get_class($this->createMock(stdClass::class));

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [$modelClass]);

        $storage->delete(new stdClass());
    }

    public function testShouldCallDoDeleteModelOnModelDelete(): void
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

    public function testThrowIfInvalidModelGivenOnGetIdentity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid model given. Should be instance of Mock_stdClass_');
        $modelClass = get_class($this->createMock(stdClass::class));

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [$modelClass]);

        $storage->identify(new stdClass());
    }

    public function testShouldCallDoGetIdentityOnGetIdentificator(): void
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

    public function testShouldReturnNullIfNotSupportedIdentityGivenOnFindModelByIdentity(): void
    {
        $modelClass = get_class($this->createMock(stdClass::class));
        $identity = new Identity('anId', new stdClass());

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [$modelClass]);

        $this->assertNull($storage->find($identity));
    }

    public function testShouldCallFindModelByIdOnFindModelByIdentityWithIdFromIdentity(): void
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

    public function testShouldCallFindModelByIdOnFindModelByEvenIfModelClassPrependWithSlash(): void
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

    public function testShouldProxyFindModelByIdResultOnFindModelByIdentity(): void
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

    public function testShouldNotCallDoFindIfIdentityClassNotMatchStorageOne(): void
    {
        $expectedModel = new stdClass();
        $identity = new Identity('aId', $expectedModel);

        $modelClass = get_class($this->createMock(stdClass::class));

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [$modelClass]);
        $storage
            ->expects($this->never())
            ->method('doFind')
        ;

        $this->assertNull($storage->find($identity));
    }

    public function testShouldReturnTrueIfModelSupportedOnSupportModel(): void
    {
        $model = $this->createMock(stdClass::class);

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [get_class($model)]);

        $this->assertTrue($storage->support($model));
    }

    public function testShouldReturnFalseIfModelNotSupportedOnSupportModel(): void
    {
        $modelClass = get_class($this->createMock(stdClass::class));

        $storage = $this->getMockForAbstractClass(AbstractStorage::class, [$modelClass]);

        $this->assertFalse($storage->support(new stdClass()));
    }
}
