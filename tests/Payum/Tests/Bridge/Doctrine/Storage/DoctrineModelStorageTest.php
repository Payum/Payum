<?php
namespace Payum\Tests\Bridge\Doctrine\Storage;

use Payum\Bridge\Doctrine\Storage\DoctrineModelStorage;
use Payum\Examples\Model\TestModel;

class DoctrineModelStorageTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        if (false == class_exists('Doctrine\ORM\Version', $autoload = true)) {
            throw new \PHPUnit_Framework_SkippedTestError('Doctrine ORM lib not installed. Have you run composer with --dev option?');
        }
    }
    
    /**
     * @test
     */
    public function shouldImplementModelStorageInterface()    
    {
        $rc = new \ReflectionClass('Payum\Bridge\Doctrine\Storage\DoctrineModelStorage');
        
        $this->assertTrue($rc->implementsInterface('Payum\Domain\Storage\ModelStorageInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithObjectManagerAndModelClassAsArguments()
    {
        new DoctrineModelStorage(
            $this->createObjectManager(),
            'Payum\Examples\Model\TestModel'
        );
    }

    /**
     * @test
     */
    public function shouldCreateInstanceOfModelClassGivenInConstructor()
    {
        $expectedModelClass = 'Payum\Examples\Model\TestModel';

        $storage = new DoctrineModelStorage(
            $this->createObjectManager(),
            $expectedModelClass
        );

        $model = $storage->createModel();

        $this->assertInstanceOf($expectedModelClass, $model);
        $this->assertNull($model->getId());
    }

    /**
     * @test
     */
    public function shouldCallObjectManagerPersistAndFlushOnUpdateModel()
    {
        $objectManagerMock = $this->createObjectManager();
        $objectManagerMock
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('Payum\Examples\Model\TestModel'))
        ;
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
        ;
        
        $storage = new DoctrineModelStorage(
            $objectManagerMock,
            'Payum\Examples\Model\TestModel'
        );

        $model = $storage->createModel();

        $storage->updateModel($model);
    }

    /**
     * @test
     */
    public function shouldFindModelById()
    {
        $expectedModelClass = 'Payum\Examples\Model\TestModel';
        $expectedModelId = 123;
        $expectedFoundModel = new TestModel;
        
        $objectManagerMock = $this->createObjectManager();
        $objectManagerMock
            ->expects($this->once())
            ->method('find')
            ->with($expectedModelClass, $expectedModelId)
            ->will($this->returnValue($expectedFoundModel))
        ;

        $storage = new DoctrineModelStorage(
            $objectManagerMock,
            'Payum\Examples\Model\TestModel'
        );

        $actualModel = $storage->findModelById($expectedModelId);
    
        $this->assertSame($expectedFoundModel, $actualModel);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid model given. Should be instance of Payum\Tests\Bridge\Doctrine\Storage\TestModel
     */
    public function throwIfTryUpdateModelNotInstanceOfModelClass()
    {
        $storage = new DoctrineModelStorage(
            $this->createObjectManager(),
            'Payum\Tests\Bridge\Doctrine\Storage\TestModel'
        );

        $storage->updateModel(new \stdClass);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Persistence\ObjectManager
     */
    protected function createObjectManager()
    {
        return $this->getMock('Doctrine\Common\Persistence\ObjectManager');    
    }
}