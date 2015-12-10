<?php
namespace Payum\Core\Tests\Bridge\Zend\Storage;

use Payum\Core\Bridge\Zend\Storage\TableGatewayStorage;
use Zend\Db\TableGateway\TableGateway;

class TableGatewayStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfAbstractStorage()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Zend\Storage\TableGatewayStorage');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Storage\AbstractStorage'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithTableGatewayAndModelClassAsArguments()
    {
        new TableGatewayStorage($this->createTableGatewayMock(), 'stdClass');
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Method is not supported by the storage.
     */
    public function throwIfTryToUseNotSupportedFindByMethod()
    {
        $storage = new TableGatewayStorage($this->createTableGatewayMock(), 'stdClass');

        $storage->findBy(array());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TableGateway
     */
    protected function createTableGatewayMock()
    {
        return $this->getMock('Zend\Db\TableGateway\TableGateway', array(), array(), '', false);
    }
}
