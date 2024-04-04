<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Entity;

use Payum\Core\Security\SensitiveValue;
use Payum\Core\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Core\Tests\Mocks\Entity\ArrayObject;

class ArrayObjectTest extends OrmTest
{
    public function testShouldAllowPersistEmpty()
    {
        $entity = new ArrayObject();
        $this->em->persist($entity);
        $this->em->flush();

        $this->assertSame([$entity], $this->em->getRepository(ArrayObject::class)->findAll());
    }

    public function testShouldAllowPersistWithSomeFieldsSet()
    {
        $model = new ArrayObject();
        $model['foo'] = 'theFoo';
        $model['bar'] = array('bar1', 'bar2' => 'theBar2');

        $this->em->persist($model);
        $this->em->flush();

        $this->assertSame([$model], $this->em->getRepository(ArrayObject::class)->findAll());
    }

    public function testShouldAllowFindPersistedArrayobject()
    {
        $model = new ArrayObject();
        $model['foo'] = 'theFoo';
        $model['bar'] = array('bar1', 'bar2' => 'theBar2');

        $this->em->persist($model);
        $this->em->flush();

        $id = $model->getId();

        $this->em->clear();

        $foundModel = $this->em->find(get_class($model), $id);

        //guard
        $this->assertNotSame($model, $foundModel);

        $this->assertEquals(iterator_to_array($model), iterator_to_array($foundModel));
    }

    public function testShouldNotStoreSensitiveValue()
    {
        $model = new ArrayObject();
        $model['cardNumber'] = new SensitiveValue('theCardNumber');

        $this->em->persist($model);
        $this->em->flush();

        $this->em->refresh($model);

        $this->assertEquals(null, $model['cardNumber']);
    }
}
