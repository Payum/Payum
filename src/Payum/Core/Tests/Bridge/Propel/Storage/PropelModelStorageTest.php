<?php

namespace Payum\Core\Tests\Bridge\Propel\Storage;

use Payum\Core\Bridge\Propel\Storage\PropelStorage;
use Payum\Core\Tests\Mocks\Model\TestModel;

class PropelModelStorageTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        /*if (false == class_exists('Propel\PropelBundle\PropelBundle', $autoload = true)) {
            throw new \PHPUnit_Framework_SkippedTestError('Propel ORM lib not installed. Have you run composer with --dev option?');
        }*/
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractStorage()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Propel\Storage\PropelStorage');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Storage\AbstractStorage'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModelClassesAsArguments()
    {
        new PropelStorage(
            'Payum\Core\Tests\Mocks\Model\TestModel',
            'Payum\Core\Tests\Mocks\Model\TestModelPeer',
            'Payum\Core\Tests\Mocks\Model\TestModelQuery'
        );
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Method is not supported by the storage.
     */
    public function throwIfTryToUseNotSupportedFindByMethod()
    {
        $storage = new PropelStorage('Payum\Core\Tests\Mocks\Model\TestModel');

        $storage->findBy(array());
    }

    /**
     * @test
     */
    public function shouldCreateInstanceOfModelClassGivenInConstructor()
    {
        $expectedModelClass = 'Payum\Core\Tests\Mocks\Model\TestModel';

        $storage = new PropelStorage($expectedModelClass);

        $model = $storage->create();

        $this->assertInstanceOf($expectedModelClass, $model);
        $this->assertNull($model->getId());
    }

    /**
     * @test
     */
    public function shouldCallModelClassSaveOnUpdateModel()
    {
        $storage = new PropelStorage('Payum\Core\Tests\Mocks\Model\TestModel');

        $model = $storage->create();

        $storage->update($model);
    }
}
