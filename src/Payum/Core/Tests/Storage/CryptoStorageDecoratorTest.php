<?php
namespace Payum\Core\Tests\Storage;

use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Payum\Core\Storage\CryptoStorageDecorator;
use Payum\Core\Storage\StorageInterface;

class CryptoStorageDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldImplementStorageInterface()
    {
        $rc = new \ReflectionClass(CryptoStorageDecorator::class);

        $this->assertTrue($rc->implementsInterface(StorageInterface::class));
    }

    public function testCouldBeConstructedWithDecoratedStorageAndCypherAsArguments()
    {
        new CryptoStorageDecorator($this->createStorageMock(), $this->createCypherMock());
    }

    public function testShouldProxyCallToDecoratedStorageAndDoNothingWithCypherOnCreate()
    {
        $decoratedStorage = $this->createStorageMock();
        $decoratedStorage
            ->expects($this->once())
            ->method('create')
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

        $storage->create();
    }

    public function testShouldProxyCallToDecoratedStorageAndDoNothingWithCypherOnSupport()
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

    public function testShouldProxyCallToDecoratedStorageAndDoNothingWithCypherOnDelete()
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

    public function testShouldProxyCallToDecoratedStorageAndDoNothingWithCypherOnIdentify()
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

    public function testShouldProxyCallToDecoratedStorageAndPassCypherToModelEncryptOnUpdate()
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
        ;
        $cypherMock
            ->expects($this->never())
            ->method('decrypt')
        ;

        $storage = new CryptoStorageDecorator($decoratedStorage, $cypherMock);

        $storage->update($model);
    }

    public function testShouldProxyCallToDecoratedStorageAndPassCypherToModelDecryptOnFind()
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
        ;

        $storage = new CryptoStorageDecorator($decoratedStorage, $cypherMock);

        $storage->find('anId');
    }

    public function testShouldProxyCallToDecoratedStorageAndPassCypherToEveryModelDecryptOnFindBy()
    {
        $decoratedStorage = $this->createStorageMock();
        $decoratedStorage
            ->expects($this->once())
            ->method('findBy')
            ->willReturn([new CryptedModel(), new CryptedModel(), new \stdClass()])
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
        ;

        $storage = new CryptoStorageDecorator($decoratedStorage, $cypherMock);

        $storage->findBy([]);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StorageInterface
     */
    private function createStorageMock()
    {
        return $this->getMock(StorageInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CypherInterface
     */
    private function createCypherMock()
    {
        return $this->getMock(CypherInterface::class);
    }
}

class CryptedModel implements CryptedInterface
{
    public function decrypt(CypherInterface $cypher)
    {
        $cypher->decrypt('theEncryptedVal');
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(CypherInterface $cypher)
    {
        $cypher->encrypt('theVal');
    }
}