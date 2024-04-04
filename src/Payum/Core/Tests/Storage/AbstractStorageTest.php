<?php
namespace Payum\Core\Tests\Storage;

use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;
use Payum\Core\Storage\IdentityInterface;
use PHPUnit\Framework\TestCase;

class AbstractStorageTest extends TestCase
{
    public function testShouldImplementStorageInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Storage\AbstractStorage');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Storage\StorageInterface'));
    }

    public function testShouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Core\Storage\AbstractStorage');

        $this->assertTrue($rc->isAbstract());
    }

    public function testShouldCreateInstanceOfModelClassSetInConstructor()
    {
        $storage = new class('stdClass') extends AbstractStorage {
            protected function doUpdateModel($model) {}
            protected function doDeleteModel($model) {}
            protected function doGetIdentity($model) {}
            protected function doFind($id) {}
            public function findBy(array $criteria) {}
        };

        $model = $storage->create();

        $this->assertInstanceOf('stdClass', $model);
    }

    public function testThrowIfInvalidModelGivenOnUpdate()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid model given. Should be instance of Mock_stdClass_');
        $modelClass = get_class($this->createMock('stdClass'));

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array($modelClass));

        $storage->update(new \stdClass());
    }

    public function testShouldCallDoUpdateModelOnModelUpdate()
    {
        $model = new \stdClass();

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array('stdClass'));
        $storage
            ->expects($this->once())
            ->method('doUpdateModel')
            ->with($this->identicalTo($model))
        ;

        $storage->update($model);
    }

    public function testThrowIfInvalidModelGivenOnDelete()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid model given. Should be instance of Mock_stdClass_');
        $modelClass = get_class($this->createMock('stdClass'));

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array($modelClass));

        $storage->delete(new \stdClass());
    }

    public function testShouldCallDoDeleteModelOnModelDelete()
    {
        $model = new \stdClass();

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array('stdClass'));
        $storage
            ->expects($this->once())
            ->method('doDeleteModel')
            ->with($this->identicalTo($model))
        ;

        $storage->delete($model);
    }

    public function testThrowIfInvalidModelGivenOnGetIdentity()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid model given. Should be instance of Mock_stdClass_');
        $modelClass = get_class($this->createMock('stdClass'));

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array($modelClass));

        $storage->identify(new \stdClass());
    }

    public function testShouldCallDoGetIdentityOnGetIdentificator()
    {
        $model = new \stdClass();

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array('stdClass'));
        $storage
            ->expects($this->once())
            ->method('doGetIdentity')
            ->with($this->identicalTo($model))
        ;

        $storage->identify($model);
    }

    public function testShouldReturnNullIfNotSupportedIdentityGivenOnFindModelByIdentity()
    {
        $modelClass = get_class($this->createMock('stdClass'));
        $identity = new Identity('anId', new \stdClass());

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array($modelClass));

        $this->assertNull($storage->find($identity));
    }

    public function testShouldCallFindModelByIdOnFindModelByIdentityWithIdFromIdentity()
    {
        $identity = new Identity('theId', new \stdClass());

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array('stdClass'));
        $storage
            ->expects($this->once())
            ->method('doFind')
            ->with('theId')
        ;

        $storage->find($identity);
    }

    public function testShouldCallFindModelByIdOnFindModelByEvenIfModelClassPrependWithSlash()
    {
        $identity = new Identity('theId', new \stdClass());

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array('\stdClass'));
        $storage
            ->expects($this->once())
            ->method('doFind')
            ->with('theId')
        ;

        $storage->find($identity);
    }

    public function testShouldProxyFindModelByIdResultOnFindModelByIdentity()
    {
        $expectedModel = new \stdClass();
        $identity = new Identity('aId', $expectedModel);

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array('stdClass'));
        $storage
            ->expects($this->once())
            ->method('doFind')
            ->willReturn($expectedModel)
        ;

        $this->assertSame($expectedModel, $storage->find($identity));
    }

    public function testShouldNotCallDoFindIfIdentityClassNotMatchStorageOne()
    {
        $expectedModel = new \stdClass();
        $identity = new Identity('aId', $expectedModel);

        $modelClass = get_class($this->createMock('stdClass'));

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array($modelClass));
        $storage
            ->expects($this->never())
            ->method('doFind')
        ;

        $this->assertNull($storage->find($identity));
    }

    public function testShouldReturnTrueIfModelSupportedOnSupportModel()
    {
        $model = $this->createMock('stdClass');

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array(get_class($model)));

        $this->assertTrue($storage->support($model));
    }

    public function testShouldReturnFalseIfModelNotSupportedOnSupportModel()
    {
        $modelClass = get_class($this->createMock('stdClass'));

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array($modelClass));

        $this->assertFalse($storage->support(new \stdClass()));
    }

    public function testShouldReturnFalseIfModelNotObjectOnSupportModel()
    {
        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array('stdClass'));

        $this->assertFalse($storage->support('notObject'));
    }
}
