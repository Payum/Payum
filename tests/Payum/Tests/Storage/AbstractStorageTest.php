<?php
namespace Payum\Tests\Storage;

use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;
use Payum\Examples\Model\TestModel;
use \Payum\Storage\FilesystemStorage;
use Payum\Storage\Identificator;

class AbstractStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementStorageInterface()
    {
        $rc = new \ReflectionClass('Payum\Storage\AbstractStorage');
        
        $this->assertTrue($rc->implementsInterface('Payum\Storage\StorageInterface'));
    }

    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Storage\AbstractStorage');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModelClassAsFirstArgument()
    {
        $this->getMockForAbstractClass('Payum\Storage\AbstractStorage', array('stdClass'));
    }

    /**
     * @test
     */
    public function shouldCreateInstanceOfModelClassSetInConstructor()
    {
        $storage = $this->getMockForAbstractClass('Payum\Storage\AbstractStorage', array('stdClass'));

        $model = $storage->createModel();

        $this->assertInstanceOf('stdClass', $model);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid model given. Should be instance of Mock_stdClass_
     */
    public function throwIfInvalidModelGivenOnUpdate()
    {
        $modelClass = get_class($this->getMock('stdClass'));

        $storage = $this->getMockForAbstractClass('Payum\Storage\AbstractStorage', array($modelClass));

        $storage->updateModel(new \stdClass);
    }

    /**
     * @test
     */
    public function shouldCallDoUpdateModelOnModelUpdate()
    {
        $model = new \stdClass;

        $storage = $this->getMockForAbstractClass('Payum\Storage\AbstractStorage', array('stdClass'));
        $storage
            ->expects($this->once())
            ->method('doUpdateModel')
            ->with($this->identicalTo($model))
        ;

        $storage->updateModel($model);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid model given. Should be instance of Mock_stdClass_
     */
    public function throwIfInvalidModelGivenOnDelete()
    {
        $modelClass = get_class($this->getMock('stdClass'));

        $storage = $this->getMockForAbstractClass('Payum\Storage\AbstractStorage', array($modelClass));

        $storage->deleteModel(new \stdClass);
    }

    /**
     * @test
     */
    public function shouldCallDoDeleteModelOnModelDelete()
    {
        $model = new \stdClass;

        $storage = $this->getMockForAbstractClass('Payum\Storage\AbstractStorage', array('stdClass'));
        $storage
            ->expects($this->once())
            ->method('doDeleteModel')
            ->with($this->identicalTo($model))
        ;

        $storage->deleteModel($model);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid model given. Should be instance of Mock_stdClass_
     */
    public function throwIfInvalidModelGivenOnGetIdentificator()
    {
        $modelClass = get_class($this->getMock('stdClass'));

        $storage = $this->getMockForAbstractClass('Payum\Storage\AbstractStorage', array($modelClass));

        $storage->getIdentificator(new \stdClass);
    }

    /**
     * @test
     */
    public function shouldCallDoGetIdentificatorOnGetIdentificator()
    {
        $model = new \stdClass;

        $storage = $this->getMockForAbstractClass('Payum\Storage\AbstractStorage', array('stdClass'));
        $storage
            ->expects($this->once())
            ->method('doGetIdentificator')
            ->with($this->identicalTo($model))
        ;

        $storage->getIdentificator($model);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid model given. Should be instance of Mock_stdClass_
     */
    public function throwIfInvalidIdentificatorGivenOnFindModelByIdentificator()
    {
        $modelClass = get_class($this->getMock('stdClass'));
        $identificator = new Identificator('anId', new \stdClass);

        $storage = $this->getMockForAbstractClass('Payum\Storage\AbstractStorage', array($modelClass));

        $storage->findModelByIdentificator($identificator);
    }

    /**
     * @test
     */
    public function shouldCallDoFindModelByIdentificatorOnFindModelByIdentificator()
    {
        $identificator = new Identificator('anId', new \stdClass);

        $storage = $this->getMockForAbstractClass('Payum\Storage\AbstractStorage', array('stdClass'));
        $storage
            ->expects($this->once())
            ->method('doFindModelByIdentificator')
            ->with($this->identicalTo($identificator))
        ;

        $storage->findModelByIdentificator($identificator);
    }
}