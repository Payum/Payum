<?php

namespace Payum\Core\Tests\Bridge\Propel\Storage;

use Payum\Core\Bridge\Propel\Storage\Propel1Storage as PropelStorage;
use Payum\Core\Tests\Mocks\Model\PropelModel;

class Propel1StorageTest extends \PHPUnit_Framework_TestCase
{
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
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Save method was triggered.
     */
    public function throwForModelClassSaveOnUpdateModel()
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

        $storage = new PropelStorage('Payum\Core\Tests\Mocks\Model\PropelModel');

        $actualModel = $storage->find($expectedModelId);

        $this->assertEquals($expectedFoundModel, $actualModel);
    }

    /**
     * @test
     */
    public function shouldFindModelByCriteria()
    {
        $expectedModelId = 123;
        $expectedFoundModel = new PropelModel();
        $expectedFoundModel->findPk($expectedModelId);

        $storage = new PropelStorage('Payum\Core\Tests\Mocks\Model\PropelModel');

        $actualModel = $storage->findBy(array('id' => $expectedModelId));

        $this->assertEquals($expectedFoundModel, $actualModel);
    }
}
