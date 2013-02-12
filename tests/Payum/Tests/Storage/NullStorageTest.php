<?php
namespace Payum\Tests\Storage;

use Payum\Storage\NullStorage;

class NullStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementStorageInterface()
    {
        $rc = new \ReflectionClass('Payum\Storage\NullStorage');
        
        $this->assertTrue($rc->implementsInterface('Payum\Storage\StorageInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new NullStorage;
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\LogicException
     * @expectedExceptionMessage The null storage cannot create a model.
     */
    public function throwIfCreateModelCalled()
    {
        $storage = new NullStorage();
        
        $model = $storage->createModel();
        
        $this->assertEquals(null, $model);
    }

    /**
     * @test
     */
    public function shouldDoNothingOnModelUpdate()
    {
        $storage = new NullStorage();
        
        $storage->updateModel(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldReturnNullOnFindModelByIdCall()
    {
        $storage = new NullStorage();

        $this->assertNull($storage->findModelById(100));
    }
}