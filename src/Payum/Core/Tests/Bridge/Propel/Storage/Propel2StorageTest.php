<?php
namespace Payum\Core\Tests\Bridge\Propel\Storage;

use Payum\Core\Bridge\Propel2\Storage\Propel2Storage as PropelStorage;
use Payum\Core\Tests\Mocks\Model\Propel2ModelQuery;
use PHPUnit\Framework\TestCase;

class Propel2StorageTest extends TestCase
{
    public function testShouldBeSubClassOfAbstractStorage()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Propel2\Storage\Propel2Storage');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Storage\AbstractStorage'));
    }

    public function testShouldCreateInstanceOfModelClassGivenInConstructor()
    {
        $expectedModelClass = 'Payum\Core\Tests\Mocks\Model\Propel2Model';

        $storage = new PropelStorage($expectedModelClass);

        $model = $storage->create();

        $this->assertInstanceOf($expectedModelClass, $model);
        $this->assertNull($model->getId());
    }

    public function throwForModelClassSaveOnUpdateModel()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('Save method was triggered.');
        $storage = new PropelStorage('Payum\Core\Tests\Mocks\Model\Propel2Model');

        $model = $storage->create();

        $storage->update($model);
    }

    public function testShouldFindModelById()
    {
        $expectedModelId = 123;
        $expectedModelQuery = new Propel2ModelQuery();
        $expectedFoundModel = $expectedModelQuery->findPk($expectedModelId);

        $storage = new PropelStorage('Payum\Core\Tests\Mocks\Model\Propel2Model');

        $actualModel = $storage->find($expectedModelId);

        $this->assertEquals($expectedFoundModel, $actualModel);
    }

    public function testShouldFindModelByCriterion()
    {
        $expectedModelId = 123;
        $expectedModelQuery = new Propel2ModelQuery();
        $expectedFoundModel = $expectedModelQuery->findPk($expectedModelId);

        $storage = new PropelStorage('Payum\Core\Tests\Mocks\Model\Propel2Model');

        $actualModel = $storage->findBy(array('id' => $expectedModelId));

        $this->assertEquals($expectedFoundModel, $actualModel);
    }

    public function testShouldFindModelByCriteria()
    {
        $expectedModelId = 123;
        $expectedModelCurrency = "USD";

        $expectedModelQuery = new Propel2ModelQuery();
        $expectedFoundModel = $expectedModelQuery
            ->filterBy("id", $expectedModelId)
            ->filterBy("currency", $expectedModelCurrency)
            ->find()
        ;

        $storage = new PropelStorage('Payum\Core\Tests\Mocks\Model\Propel2Model');

        $actualModel = $storage->findBy(array(
            'id' => $expectedModelId,
            'currency' => $expectedModelCurrency
        ));

        $this->assertEquals($expectedFoundModel, $actualModel);
    }
}
