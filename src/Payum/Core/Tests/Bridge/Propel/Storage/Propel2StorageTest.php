<?php
namespace Payum\Core\Tests\Bridge\Propel\Storage;

use Payum\Core\Bridge\Propel2\Storage\Propel2Storage as PropelStorage;
use Payum\Core\Tests\Mocks\Model\Propel2ModelQuery;

class Propel2StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractStorage()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Propel2\Storage\Propel2Storage');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Storage\AbstractStorage'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModelClassesAsArguments()
    {
        new PropelStorage(
            'Payum\Core\Tests\Mocks\Model\Propel2Model',
            'Payum\Core\Tests\Mocks\Model\Propel2Query'
        );
    }

    /**
     * @test
     */
    public function shouldCreateInstanceOfModelClassGivenInConstructor()
    {
        $expectedModelClass = 'Payum\Core\Tests\Mocks\Model\Propel2Model';

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
        $storage = new PropelStorage('Payum\Core\Tests\Mocks\Model\Propel2Model');

        $model = $storage->create();

        $storage->update($model);
    }

    /**
     * @test
     */
    public function shouldFindModelById()
    {
        $expectedModelId = 123;
        $expectedModelQuery = new Propel2ModelQuery();
        $expectedFoundModel = $expectedModelQuery->findPk($expectedModelId);

        $storage = new PropelStorage('Payum\Core\Tests\Mocks\Model\Propel2Model');

        $actualModel = $storage->find($expectedModelId);

        $this->assertEquals($expectedFoundModel, $actualModel);
    }

    /**
     * @test
     */
    public function shouldFindModelByCriterion()
    {
        $expectedModelId = 123;
        $expectedModelQuery = new Propel2ModelQuery();
        $expectedFoundModel = $expectedModelQuery->findPk($expectedModelId);

        $storage = new PropelStorage('Payum\Core\Tests\Mocks\Model\Propel2Model');

        $actualModel = $storage->findBy(array('id' => $expectedModelId));

        $this->assertEquals($expectedFoundModel, $actualModel);
    }

    /**
     * @test
     */
    public function shouldFindModelByCriteria()
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
