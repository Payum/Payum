<?php
namespace Payum\Tests\Request\Storage;

use Payum\Request\Storage\FilesystemRequestStorage;
use Payum\Request\SimpleSellRequest as BaseSimpleSellRequest;

class FilesystemRequestStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementRequestStorageInterface()
    {
        $rc = new \ReflectionClass('Payum\Request\Storage\FilesystemRequestStorage');
        
        $this->assertTrue($rc->implementsInterface('Payum\Request\Storage\RequestStorageInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithStorageDirRequestClassAndIdPropertyArguments()
    {
        new FilesystemRequestStorage(
            sys_get_temp_dir(), 
            'Payum\Tests\Request\Storage\SimpleSellRequest',
            'id'
        );
    }

    /**
     * @test
     */
    public function shouldCreateInstanceOfRequestClassGivenInConstructor()
    {
        $expectedRequestClass = 'Payum\Tests\Request\Storage\SimpleSellRequest';
        
        $storage = new FilesystemRequestStorage(
            sys_get_temp_dir(),
            $expectedRequestClass,
            'id'
        );
        
        $request = $storage->createRequest();
        
        $this->assertInstanceOf($expectedRequestClass, $request);
        $this->assertNull($request->getId());
    }

    /**
     * @test
     */
    public function shouldUpdateRequestAndSetIdToRequest()
    {
        $expectedRequestClass = 'Payum\Tests\Request\Storage\SimpleSellRequest';

        $storage = new FilesystemRequestStorage(
            sys_get_temp_dir(),
            $expectedRequestClass,
            'id'
        );

        $request = $storage->createRequest();
        
        $storage->updateRequest($request);

        $this->assertInstanceOf($expectedRequestClass, $request);
        $this->assertNotEmpty($request->getId());
    }

    /**
     * @test
     */
    public function shouldKeepIdTheSameOnSeveralUpdates()
    {
        $storage = new FilesystemRequestStorage(
            sys_get_temp_dir(),
            'Payum\Tests\Request\Storage\SimpleSellRequest',
            'id'
        );

        $request = $storage->createRequest();

        $storage->updateRequest($request);
        $firstId = $request->getId();

        $storage->updateRequest($request);
        $secondId = $request->getId();

        $this->assertSame($firstId, $secondId);
    }

    /**
     * @test
     */
    public function shouldCreateFileWithRequestInfoInStorageDirOnUpdateRequest()
    {
        $storage = new FilesystemRequestStorage(
            sys_get_temp_dir(),
            'Payum\Tests\Request\Storage\SimpleSellRequest',
            'id'
        );

        $request = $storage->createRequest();
        $storage->updateRequest($request);
        
        $this->assertFileExists(sys_get_temp_dir().'/request-'.$request->getId());
    }

    /**
     * @test
     */
    public function shouldGenerateDifferentIdsForDifferentRequests()
    {
        $storage = new FilesystemRequestStorage(
            sys_get_temp_dir(),
            'Payum\Tests\Request\Storage\SimpleSellRequest',
            'id'
        );

        $requestOne = $storage->createRequest();
        $storage->updateRequest($requestOne);

        $requestTwo = $storage->createRequest();
        $storage->updateRequest($requestTwo);

        $this->assertNotSame($requestOne->getId(), $requestTwo->getId());
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid request given. Should be instance of Payum\Tests\Request\Storage\SimpleSellRequest
     */
    public function throwIfTryUpdateRequestNotInstanceOfRequestClass()
    {
        $storage = new FilesystemRequestStorage(
            sys_get_temp_dir(),
            'Payum\Tests\Request\Storage\SimpleSellRequest',
            'id'
        );
        
        $storage->updateRequest(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldFindRequestById()
    {
        $storage = new FilesystemRequestStorage(
            sys_get_temp_dir(),
            'Payum\Tests\Request\Storage\SimpleSellRequest',
            'id'
        );
        
        $request = $storage->createRequest();
        $storage->updateRequest($request);

        $foundRequest = $storage->findRequestById($request->getId());
        
        $this->assertNotSame($request, $foundRequest);
        $this->assertEquals($request->getId(), $foundRequest->getId());
    }

    /**
     * @test
     */
    public function shouldStoreInfoBetweenUpdateAndFind()
    {
        $storage = new FilesystemRequestStorage(
            sys_get_temp_dir(),
            'Payum\Tests\Request\Storage\SimpleSellRequest',
            'id'
        );

        $request = $storage->createRequest();
        $request->setPrice($expectedPrice = 123);
        $request->setCurrency($expectedCurrency = 'FOO');
        
        $storage->updateRequest($request);

        $foundRequest = $storage->findRequestById($request->getId());

        $this->assertNotSame($request, $foundRequest);
        $this->assertEquals($expectedPrice, $foundRequest->getPrice());
        $this->assertEquals($expectedCurrency, $foundRequest->getCurrency());
    }
}

class SimpleSellRequest extends BaseSimpleSellRequest
{
    /**
     * @var
     */
    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
