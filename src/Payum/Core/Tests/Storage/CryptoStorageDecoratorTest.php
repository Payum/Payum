<?php

namespace Payum\Core\Tests\Storage;

use LogicException;
use Payum\Core\Model\Identity;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Payum\Core\Storage\CryptoStorageDecorator;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CryptoStorageDecoratorTest extends TestCase
{
    public function testShouldImplementStorageInterface(): void
    {
        $rc = new ReflectionClass(CryptoStorageDecorator::class);

        $this->assertTrue($rc->implementsInterface(StorageInterface::class));
    }

    public function testShouldProxyCallToDecoratedStorageAndDoNothingWithCypherOnCreate(): void
    {
        $model = new CryptedModel();

        $decoratedStorage = $this->createStorageMock();
        $decoratedStorage
            ->expects($this->once())
            ->method('create')
            ->willReturn($model)
        ;

        $cypherMock = $this->createCypherMock();
        $cypherMock
            ->expects($this->never())
            ->method('encrypt')
        ;
        $cypherMock
            ->expects($this->never())
            ->method('decrypt')
        ;

        $storage = new CryptoStorageDecorator($decoratedStorage, $cypherMock);

        $createdModel = $storage->create();

        $this->assertSame($model, $createdModel);
    }

    public function testThrowsIfModelDoesImplementCryptedInterfaceOnCreate(): void
    {
        $decoratedStorage = $this->createStorageMock();
        $decoratedStorage
            ->expects($this->once())
            ->method('create')
            ->willReturn(new stdClass())
        ;

        $storage = new CryptoStorageDecorator($decoratedStorage, $this->createCypherMock());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The model stdClass must implement Payum\Core\Security\CryptedInterface interface.');
        $storage->create();
    }

    public function testShouldProxyCallToDecoratedStorageAndDoNothingWithCypherOnSupport(): void
    {
        $model = new CryptedModel();

        $decoratedStorage = $this->createStorageMock();
        $decoratedStorage
            ->expects($this->once())
            ->method('support')
            ->with($this->identicalTo($model))
        ;

        $cypherMock = $this->createCypherMock();
        $cypherMock
            ->expects($this->never())
            ->method('encrypt')
        ;
        $cypherMock
            ->expects($this->never())
            ->method('decrypt')
        ;

        $storage = new CryptoStorageDecorator($decoratedStorage, $cypherMock);

        $storage->support($model);
    }

    public function testShouldProxyCallToDecoratedStorageAndDoNothingWithCypherOnDelete(): void
    {
        $model = new CryptedModel();

        $decoratedStorage = $this->createStorageMock();
        $decoratedStorage
            ->expects($this->once())
            ->method('delete')
            ->with($this->identicalTo($model))
        ;

        $cypherMock = $this->createCypherMock();
        $cypherMock
            ->expects($this->never())
            ->method('encrypt')
        ;
        $cypherMock
            ->expects($this->never())
            ->method('decrypt')
        ;

        $storage = new CryptoStorageDecorator($decoratedStorage, $cypherMock);

        $storage->delete($model);
    }

    public function testShouldProxyCallToDecoratedStorageAndDoNothingWithCypherOnIdentify(): void
    {
        $model = new CryptedModel();

        $decoratedStorage = $this->createStorageMock();
        $decoratedStorage
            ->expects($this->once())
            ->method('identify')
            ->with($this->identicalTo($model))
        ;

        $cypherMock = $this->createCypherMock();
        $cypherMock
            ->expects($this->never())
            ->method('encrypt')
        ;
        $cypherMock
            ->expects($this->never())
            ->method('decrypt')
        ;

        $storage = new CryptoStorageDecorator($decoratedStorage, $cypherMock);

        $storage->identify($model);
    }

    public function testShouldProxyCallToDecoratedStorageAndPassCypherToModelEncryptOnUpdate(): void
    {
        $model = new CryptedModel();

        $decoratedStorage = $this->createStorageMock();
        $decoratedStorage
            ->expects($this->once())
            ->method('update')
            ->willReturn($model)
        ;

        $cypherMock = $this->createCypherMock();
        $cypherMock
            ->expects($this->once())
            ->method('encrypt')
            ->with('theVal');

        $cypherMock
            ->expects($this->never())
            ->method('decrypt')
        ;

        $storage = new CryptoStorageDecorator($decoratedStorage, $cypherMock);

        $storage->update($model);
    }

    public function testThrowsIfModelDoesImplementCryptedInterfaceOnUpdate(): void
    {
        /** @var CryptoStorageDecorator<stdClass> $storage */
        $storage = new CryptoStorageDecorator($this->createStorageMock(), $this->createCypherMock());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The model stdClass must implement Payum\Core\Security\CryptedInterface interface.');
        $storage->update(new stdClass());
    }

    public function testShouldProxyCallToDecoratedStorageAndPassCypherToModelDecryptOnFind(): void
    {
        $model = new CryptedModel();

        $decoratedStorage = $this->createStorageMock();
        $decoratedStorage
            ->expects($this->once())
            ->method('find')
            ->willReturn($model)
        ;

        $cypherMock = $this->createCypherMock();
        $cypherMock
            ->expects($this->never())
            ->method('encrypt')
        ;
        $cypherMock
            ->expects($this->once())
            ->method('decrypt')
            ->with('theEncryptedVal');

        $storage = new CryptoStorageDecorator($decoratedStorage, $cypherMock);

        $foundModel = $storage->find(new Identity('anId', CryptedModel::class));

        $this->assertSame($model, $foundModel);
    }

    public function testThrowsIfModelDoesImplementCryptedInterfaceOnFind(): void
    {
        $decoratedStorage = $this->createStorageMock();
        $decoratedStorage
            ->expects($this->once())
            ->method('find')
            ->willReturn(new stdClass())
        ;

        $storage = new CryptoStorageDecorator($decoratedStorage, $this->createCypherMock());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The model stdClass must implement Payum\Core\Security\CryptedInterface interface.');
        $storage->find(new Identity('anId', CryptedModel::class));
    }

    public function testShouldProxyCallToDecoratedStorageAndPassCypherToEveryModelDecryptOnFindBy(): void
    {
        $models = [new CryptedModel(), new CryptedModel()];

        $decoratedStorage = $this->createStorageMock();
        $decoratedStorage
            ->expects($this->once())
            ->method('findBy')
            ->willReturn($models)
        ;

        $cypherMock = $this->createCypherMock();
        $cypherMock
            ->expects($this->never())
            ->method('encrypt')
        ;
        $cypherMock
            ->expects($this->exactly(2))
            ->method('decrypt')
            ->with('theEncryptedVal');

        $storage = new CryptoStorageDecorator($decoratedStorage, $cypherMock);

        $foundModels = $storage->findBy([]);

        $this->assertSame($models, $foundModels);
    }

    public function testThrowsIfModelDoesImplementCryptedInterfaceOnFindBy(): void
    {
        $decoratedStorage = $this->createStorageMock();
        $decoratedStorage
            ->expects($this->once())
            ->method('findBy')
            ->willReturn([new stdClass()])
        ;

        $storage = new CryptoStorageDecorator($decoratedStorage, $this->createCypherMock());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The model stdClass must implement Payum\Core\Security\CryptedInterface interface.');
        $storage->findBy([]);
    }

    /**
     * @return MockObject|StorageInterface<CryptedModel>
     */
    private function createStorageMock(): MockObject | StorageInterface
    {
        return $this->createMock(StorageInterface::class);
    }

    private function createCypherMock(): MockObject | CypherInterface
    {
        return $this->createMock(CypherInterface::class);
    }
}

class CryptedModel implements CryptedInterface
{
    public function decrypt(CypherInterface $cypher): void
    {
        $cypher->decrypt('theEncryptedVal');
    }

    public function encrypt(CypherInterface $cypher): void
    {
        $cypher->encrypt('theVal');
    }
}
