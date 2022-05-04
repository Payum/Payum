<?php

namespace Payum\Core\Tests\Bridge\Propel\Storage;

use Payum\Core\Bridge\Propel\Storage\Propel1Storage as PropelStorage;
use Payum\Core\Tests\Mocks\Model\PropelModel;
use PHPUnit\Framework\TestCase;

class Propel1StorageTest extends TestCase
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
    public function shouldCreateInstanceOfModelClassGivenInConstructor()
    {
        $expectedModelClass = 'Payum\Core\Tests\Mocks\Model\PropelModel';

        $storage = new PropelStorage($expectedModelClass);

        $model = $storage->create();

        $this->assertInstanceOf($expectedModelClass, $model);
        $this->assertNull($model->getId());
    }

    public function throwForModelClassSaveOnUpdateModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('Save method was triggered.');
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
