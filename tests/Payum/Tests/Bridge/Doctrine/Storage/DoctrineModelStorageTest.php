<?php
namespace Payum\Tests\Bridge\Doctrine\Storage;

use Payum\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Examples\Model\TestModel;

class DoctrineStorageTest extends \PHPUnit_Framework_TestCase
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
    public function shouldImplementStorageInterface()    
    {
        $rc = new \ReflectionClass('Payum\Bridge\Doctrine\Storage\DoctrineStorage');
        
        $this->assertTrue($rc->implementsInterface('Payum\Storage\StorageInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithObjectManagerAndModelClassAsArguments()
    {
        new DoctrineStorage(
            $this->createObjectManagerMock(),
            'Payum\Examples\Model\TestModel'
        );
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfSupportedModelGiven()
    {
        $storage = new DoctrineStorage(
            $this->createObjectManagerMock(),
            'Payum\Examples\Model\TestModel'
        );

        $this->assertTrue($storage->supportModel(new TestModel));
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfSupportedModelClassGiven()
    {
        $storage = new DoctrineStorage(
            $this->createObjectManagerMock(),
            'Payum\Examples\Model\TestModel'
        );

        $this->assertTrue($storage->supportModel('Payum\Examples\Model\TestModel'));
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfNotSupportedModelGiven()
    {
        $storage = new DoctrineStorage(
            $this->createObjectManagerMock(),
            'Payum\Examples\Model\TestModel'
        );

        $this->assertFalse($storage->supportModel(new \stdClass));
    }

    /**
     * @test
     */
    public function shouldCreateInstanceOfModelClassGivenInConstructor()
    {
        $expectedModelClass = 'Payum\Examples\Model\TestModel';

        $storage = new DoctrineStorage(
            $this->createObjectManagerMock(),
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
        $objectManagerMock = $this->createObjectManagerMock();
        $objectManagerMock
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('Payum\Examples\Model\TestModel'))
        ;
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
        ;
        
        $storage = new DoctrineStorage(
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
        
        $objectManagerMock = $this->createObjectManagerMock();
        $objectManagerMock
            ->expects($this->once())
            ->method('find')
            ->with($expectedModelClass, $expectedModelId)
            ->will($this->returnValue($expectedFoundModel))
        ;

        $storage = new DoctrineStorage(
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
    public function throwIfTryUpdateNotSupportedModel()
    {
        $storage = new DoctrineStorage(
            $this->createObjectManagerMock(),
            'Payum\Tests\Bridge\Doctrine\Storage\TestModel'
        );

        $notSupportedModel = new \stdClass;
        
        //guard
        $this->assertFalse($storage->supportModel($notSupportedModel));

        $storage->updateModel($notSupportedModel);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid model given. Should be instance of Payum\Tests\Bridge\Doctrine\Storage\TestModel
     */
    public function throwIfTryGetIdentifierOfNotSupportedModel()
    {
        $storage = new DoctrineStorage(
            $this->createObjectManagerMock(),
            'Payum\Tests\Bridge\Doctrine\Storage\TestModel'
        );

        $notSupportedModel = new \stdClass;

        //guard
        $this->assertFalse($storage->supportModel($notSupportedModel));

        $storage->getIdentificator($notSupportedModel);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Persistence\ObjectManager
     */
    protected function createObjectManagerMock()
    {
        return $this->getMock('Doctrine\Common\Persistence\ObjectManager');    
    }
}