<?php
namespace Payum\Core\Tests\Bridge\Zend\Storage;

use Payum\Core\Bridge\Zend\Storage\TableGatewayStorage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Laminas\Db\TableGateway\TableGateway;

class TableGatewayStorageTest extends TestCase
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
     */
    public function throwIfTryToUseNotSupportedFindByMethod()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('Method is not supported by the storage.');
        $storage = new TableGatewayStorage($this->createTableGatewayMock(), 'stdClass');

        $storage->findBy(array());
    }

    /**
     * @return MockObject|TableGateway
     */
    protected function createTableGatewayMock()
    {
        return $this->createMock(TableGateway::class);
    }
}
