<?php

namespace Payum\Core\Tests\Bridge\Zend\Storage;

use Laminas\Db\TableGateway\TableGateway;
use Payum\Core\Bridge\Zend\Storage\TableGatewayStorage;
use Payum\Core\Exception\LogicException;
use Payum\Core\Storage\AbstractStorage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class TableGatewayStorageTest extends TestCase
{
    public function testShouldBeSubClassOfAbstractStorage()
    {
        $rc = new ReflectionClass(TableGatewayStorage::class);

        $this->assertTrue($rc->isSubclassOf(AbstractStorage::class));
    }

    public function testThrowIfTryToUseNotSupportedFindByMethod()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Method is not supported by the storage.');
        $storage = new TableGatewayStorage($this->createTableGatewayMock(), stdClass::class);

        $storage->findBy([]);
    }

    /**
     * @return MockObject|TableGateway
     */
    protected function createTableGatewayMock()
    {
        return $this->createMock(TableGateway::class);
    }
}
