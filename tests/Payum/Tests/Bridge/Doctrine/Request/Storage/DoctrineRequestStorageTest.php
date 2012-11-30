<?php
namespace Payum\Tests\Bridge\Doctrine\Request\Storage;

use Payum\Bridge\Doctrine\Request\Storage\DoctrineRequestStorage;
use Payum\Request\SimpleSellRequest;

class DoctrineRequestStorageTest extends \PHPUnit_Framework_TestCase
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
    public function shouldImplementRequestStorageInterface()    
    {
        $rc = new \ReflectionClass('Payum\Bridge\Doctrine\Request\Storage\DoctrineRequestStorage');
        
        $this->assertTrue($rc->implementsInterface('Payum\Request\Storage\RequestStorageInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithObjectManagerAndRequestClassAsArguments()
    {
        new DoctrineRequestStorage(
            $this->createObjectManager(),
            'Payum\Request\SimpleSellRequest'
        );
    }

    /**
     * @test
     */
    public function shouldCreateInstanceOfRequestClassGivenInConstructor()
    {
        $expectedRequestClass = 'Payum\Request\SimpleSellRequest';

        $storage = new DoctrineRequestStorage(
            $this->createObjectManager(),
            'Payum\Request\SimpleSellRequest'
        );

        $request = $storage->createRequest();

        $this->assertInstanceOf($expectedRequestClass, $request);
        $this->assertNull($request->getId());
    }

    /**
     * @test
     */
    public function shouldCallObjectManagerPersistAndFlushOnUpdateRequest()
    {
        $objectManagerMock = $this->createObjectManager();
        $objectManagerMock
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('Payum\Request\SimpleSellRequest'))
        ;
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
        ;
        
        $storage = new DoctrineRequestStorage(
            $objectManagerMock,
            'Payum\Request\SimpleSellRequest'
        );

        $request = $storage->createRequest();

        $storage->updateRequest($request);
    }

    /**
     * @test
     */
    public function shouldFindRequestById()
    {
        $expectedRequestClass = 'Payum\Request\SimpleSellRequest';
        $expectedRequestId = 123;
        $expectedFoundRequest = new SimpleSellRequest;
        
        $objectManagerMock = $this->createObjectManager();
        $objectManagerMock
            ->expects($this->once())
            ->method('find')
            ->with($expectedRequestClass, $expectedRequestId)
            ->will($this->returnValue($expectedFoundRequest))
        ;

        $storage = new DoctrineRequestStorage(
            $objectManagerMock,
            'Payum\Request\SimpleSellRequest'
        );

        $actualRequest = $storage->findRequestById($expectedRequestId);
    
        $this->assertSame($expectedFoundRequest, $actualRequest);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid request given. Should be instance of Payum\Request\SimpleSellRequest
     */
    public function throwIfTryUpdateRequestNotInstanceOfRequestClass()
    {
        $storage = new DoctrineRequestStorage(
            $this->createObjectManager(),
            'Payum\Request\SimpleSellRequest'
        );

        $storage->updateRequest(new \stdClass());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Doctrine\Common\Persistence\ObjectManager
     */
    protected function createObjectManager()
    {
        return $this->getMock('Doctrine\Common\Persistence\ObjectManager');    
    }
}