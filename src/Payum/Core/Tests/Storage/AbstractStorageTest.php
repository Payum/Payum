<?php
namespace Payum\Core\Tests\Storage;

use Payum\Core\Model\Identity;
use PHPUnit\Framework\TestCase;

class AbstractStorageTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementStorageInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Storage\AbstractStorage');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Storage\StorageInterface'));
    }

    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Core\Storage\AbstractStorage');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModelClassAsFirstArgument()
    {
        $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array('stdClass'));
    }

    /**
     * @test
     */
    public function shouldCreateInstanceOfModelClassSetInConstructor()
    {
        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array('stdClass'));

        $model = $storage->create();

        $this->assertInstanceOf('stdClass', $model);
    }

    /**
     * @test
     */
    public function throwIfInvalidModelGivenOnUpdate()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid model given. Should be instance of Mock_stdClass_');
        $modelClass = get_class($this->createMock('stdClass'));

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array($modelClass));

        $storage->update(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldCallDoUpdateModelOnModelUpdate()
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

    /**
     * @test
     */
    public function throwIfInvalidModelGivenOnDelete()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid model given. Should be instance of Mock_stdClass_');
        $modelClass = get_class($this->createMock('stdClass'));

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array($modelClass));

        $storage->delete(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldCallDoDeleteModelOnModelDelete()
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

    /**
     * @test
     */
    public function throwIfInvalidModelGivenOnGetIdentity()
    {
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid model given. Should be instance of Mock_stdClass_');
        $modelClass = get_class($this->createMock('stdClass'));

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array($modelClass));

        $storage->identify(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldCallDoGetIdentityOnGetIdentificator()
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

    /**
     * @test
     */
    public function shouldReturnNullIfNotSupportedIdentityGivenOnFindModelByIdentity()
    {
        $modelClass = get_class($this->createMock('stdClass'));
        $identity = new Identity('anId', new \stdClass());

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array($modelClass));

        $this->assertNull($storage->find($identity));
    }

    /**
     * @test
     */
    public function shouldCallFindModelByIdOnFindModelByIdentityWithIdFromIdentity()
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

    /**
     * @test
     */
    public function shouldCallFindModelByIdOnFindModelByEvenIfModelClassPrependWithSlash()
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

    /**
     * @test
     */
    public function shouldProxyFindModelByIdResultOnFindModelByIdentity()
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

    /**
     * @test
     */
    public function shouldNotCallDoFindIfIdentityClassNotMatchStorageOne()
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

    /**
     * @test
     */
    public function shouldReturnTrueIfModelSupportedOnSupportModel()
    {
        $model = $this->createMock('stdClass');

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array(get_class($model)));

        $this->assertTrue($storage->support($model));
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfModelNotSupportedOnSupportModel()
    {
        $modelClass = get_class($this->createMock('stdClass'));

        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array($modelClass));

        $this->assertFalse($storage->support(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfModelNotObjectOnSupportModel()
    {
        $storage = $this->getMockForAbstractClass('Payum\Core\Storage\AbstractStorage', array('stdClass'));

        $this->assertFalse($storage->support('notObject'));
    }
}
