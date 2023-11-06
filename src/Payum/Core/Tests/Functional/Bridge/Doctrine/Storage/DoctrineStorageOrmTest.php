<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Storage;

use Payum\Core\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Tests\Mocks\Entity\TestModel;

class DoctrineStorageOrmTest extends OrmTest
{
    public function testShouldUpdateModelAndSetId()
    {
        $storage = new DoctrineStorage(
            $this->em,
            'Payum\Core\Tests\Mocks\Entity\TestModel'
        );

        $model = $storage->create();

        $storage->update($model);

        $this->assertNotNull($model->getId());
    }

    public function testShouldGetModelIdentifier()
    {
        $storage = new DoctrineStorage(
            $this->em,
            'Payum\Core\Tests\Mocks\Entity\TestModel'
        );

        $model = $storage->create();

        $storage->update($model);

        $this->assertNotNull($model->getId());

        $identity = $storage->identify($model);

        $this->assertInstanceOf('Payum\Core\Model\Identity', $identity);
        $this->assertInstanceOf($identity->getClass(), $model);
        $this->assertEquals($model->getId(), $identity->getId());
    }

    public function testShouldFindModelById()
    {
        $storage = new DoctrineStorage(
            $this->em,
            'Payum\Core\Tests\Mocks\Entity\TestModel'
        );

        $model = $storage->create();

        $storage->update($model);

        $requestId = $model->getId();

        $this->em->clear();

        $model = $storage->find($requestId);

        $this->assertInstanceOf('Payum\Core\Tests\Mocks\Entity\TestModel', $model);
        $this->assertEquals($requestId, $model->getId());
    }

    public function testShouldFindModelByIdentity()
    {
        $storage = new DoctrineStorage(
            $this->em,
            'Payum\Core\Tests\Mocks\Entity\TestModel'
        );

        $model = $storage->create();

        $storage->update($model);

        $requestId = $model->getId();

        $this->em->clear();

        $identity = $storage->identify($model);

        $foundModel = $storage->find($identity);

        $this->assertInstanceOf('Payum\Core\Tests\Mocks\Entity\TestModel', $foundModel);
        $this->assertEquals($requestId, $foundModel->getId());
    }

    public function testShouldFindByCurrency()
    {
        $storage = new DoctrineStorage(
            $this->em,
            'Payum\Core\Tests\Mocks\Entity\TestModel'
        );

        /** @var TestModel $model */
        $model = $storage->create();
        $model->setCurrency('USD');
        $storage->update($model);

        /** @var TestModel $model */
        $model = $storage->create();
        $model->setCurrency('USD');
        $storage->update($model);

        /** @var TestModel $model */
        $model = $storage->create();
        $model->setCurrency('EUR');
        $storage->update($model);


        $result = $storage->findBy(array(
            'currency' => 'USD'
        ));

        $this->assertCount(2, $result);
        $this->assertContainsOnly('Payum\Core\Tests\Mocks\Entity\TestModel', $result);

        $result = $storage->findBy(array(
            'currency' => 'EUR'
        ));

        $this->assertCount(1, $result);
        $this->assertContainsOnly('Payum\Core\Tests\Mocks\Entity\TestModel', $result);
    }

    public function testShouldFindByAllIfCriteriaIsEmpty()
    {
        $storage = new DoctrineStorage(
            $this->em,
            'Payum\Core\Tests\Mocks\Entity\TestModel'
        );

        /** @var TestModel $model */
        $model = $storage->create();
        $model->setCurrency('USD');
        $storage->update($model);

        /** @var TestModel $model */
        $model = $storage->create();
        $model->setCurrency('USD');
        $storage->update($model);

        /** @var TestModel $model */
        $model = $storage->create();
        $model->setCurrency('EUR');
        $storage->update($model);


        $result = $storage->findBy(array());

        $this->assertCount(3, $result);
        $this->assertContainsOnly('Payum\Core\Tests\Mocks\Entity\TestModel', $result);
    }
}
