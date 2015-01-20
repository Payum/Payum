<?php

namespace Payum\Core\Tests\Bridge\Propel\Storage;

use Payum\Core\Bridge\Propel\Storage\Propel1Storage as PropelStorage;
use Payum\Core\Tests\Mocks\Model\PropelModel;

class Propel1StorageTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        if (false == class_exists('Propel', $autoload = true)) {
            throw new \PHPUnit_Framework_SkippedTestError('Propel ORM lib not installed. Have you run composer with --dev option?');
        }
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractStorage()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Propel\Storage\Propel1Storage');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Storage\AbstractStorage'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModelClassesAsArguments()
    {
        new PropelStorage(
            'Payum\Core\Tests\Mocks\Model\PropelModel',
            'Payum\Core\Tests\Mocks\Model\PropelModelPeer',
            'Payum\Core\Tests\Mocks\Model\PropelModelQuery'
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
        $storage = new PropelStorage('Payum\Core\Tests\Mocks\Model\PropelModel');

        $storage->findBy(array());
    }

    /**
     * @test
     */
    public function shouldCreateInstanceOfModelClassGivenInConstructor()
    {
        $expectedModelClass = 'Payum\Core\Tests\Mocks\Model\PropelModel';

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
        $storage = new PropelStorage('Payum\Core\Tests\Mocks\Model\PropelModel');

        $model = $storage->create();

        $storage->update($model);
    }

    /**
     * @test
     */
    public function shouldFindModelById()
    {
        $expectedModelId = 123;
        $expectedFoundModel = new PropelModel();
        $expectedFoundModel->findPk($expectedModelId);

        $storage = new PropelStorage(
            'Payum\Core\Tests\Mocks\Model\PropelModel'
        );

        $actualModel = $storage->find($expectedModelId);

        $this->assertEquals($expectedFoundModel, $actualModel);
    }
}
