<?php
namespace Payum\Tests\Domain\Storage;

use Payum\Domain\Storage\NullModelStorage;

/**
 *
 */
class NullModelStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementModelStorageInterface()
    {
        $rc = new \ReflectionClass('Payum\Domain\Storage\NullModelStorage');
        
        $this->assertTrue($rc->implementsInterface('Payum\Domain\Storage\ModelStorageInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithAnyParameters()
    {
        new NullModelStorage(null, array(), true);
    }

    /**
     * @test
     */
    public function shouldNoyCreateInstanceOfModelClassGivenInConstructor()
    {
        $storage = new NullModelStorage();
        
        $model = $storage->createModel();
        
        $this->assertEquals(null, $model);
    }

    /**
     * @test
     */
    public function shouldNotUpdateModel()
    {
        $storage = new NullModelStorage();
        
        $model = $storage->updateModel(new \Payum\Domain\SimpleSell());

        $this->assertEquals(null, $model);
    }

    /**
     * @test
     */
    public function shouldNotFindModelById()
    {
        $storage = new NullModelStorage();

        $foundModel = $storage->findModelById(100);

        $this->assertEquals(null, $foundModel);
    }
}
