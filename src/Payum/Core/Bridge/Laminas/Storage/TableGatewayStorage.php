<?php

namespace Payum\Core\Bridge\Laminas\Storage;

use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\TableGateway\TableGateway as LaminasTableGateway;
use Payum\Core\Exception\LogicException;
use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;
use Payum\Core\Storage\IdentityInterface;
use ReflectionProperty;
use function method_exists;

/**
 * A Storage class for use with Payum's StorageExtension, which uses Laminas Framework's TableGateway database abstraction
 * classes. The bulk of the work is handled by the TableGateway passed in via the constructor. This can be configured
 * with hydrators and an object prototype (in a HydratingResultSet) to control what model is returned / handled and how
 * it is persisted in the database.
 *
 * E.g. Your service factory could be as follows (values in <chevrons> may need to be altered for your implementation
 * and you may wish to replace fully qualified classnames with aliases and use statements for brevity and readability):
 *    [
 *        'storageDetail' => function ($sm) {
 *            return new \Payum\Bridge\Laminas\Storage\TableGatewayStorage(
 *                $sm->get('storageDetailTableGateway'),  // Your configured table gateway (defined below).
 *                '<Payment\Model\Transaction>'           // The classname of the entity that represents your data.
 *            );
 *        },
 *        'storageDetailTableGateway' => function ($sm) {
 *            return new \Laminas\Db\TableGateway\TableGateway(
 *                '<transaction>',                        // Your database table name.
 *                $sm->get('<Laminas\Db\Adapter\Adapter>'),  // Your configured database adapter.
 *                null,
 *                new HydratingResultSet(
 *                    $sm->get('<transactionHydrator>'),  // Hydrator to hydrate your data entity (defined below).
 *                    $sm->get('<transactionEntity>')     // The entity that represents your data (defined below).
 *                 )
 *            );
 *        },
 *        'transactionHydrator' => function ($sm) {
 *            return new <\Laminas\Stdlib\Hydrator\ClassMethods()>;
 *        },
 *        'transactionEntity' => function ($sm) {
 *            return new <\Payment\Model\Transaction()>;  // The entity that represents your data (usually matching the
 *                                                           string passed in parameter two of TableGateway above).
 *        },
 *    ]
 * @template T of object
 * @extends AbstractStorage<T>
 */
class TableGatewayStorage extends AbstractStorage
{
    protected LaminasTableGateway $tableGateway;

    protected string $idField;

    public function __construct(LaminasTableGateway $tableGateway, string $modelClass, string $idField = 'id')
    {
        parent::__construct($modelClass);

        $this->tableGateway = $tableGateway;
        $this->idField = $idField;
        $this->modelClass = $modelClass;
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return T[]
     */
    public function findBy(array $criteria): array
    {
        throw new LogicException('Method is not supported by the storage.');
    }

    protected function doFind(mixed $id): ?object
    {
        return $this->tableGateway->select([
            "{$this->idField} = ?" => $id,
        ]);
    }

    protected function doUpdateModel(object $model): object
    {
        /** @var HydratingResultSet $resultSet */
        $resultSet = $this->tableGateway->getResultSetPrototype();

        if ($id = $this->getModelId($model)) {
            $this->tableGateway->update(
                $resultSet->getHydrator()->extract($model),
                [
                    "{$this->idField} = ?" => $id,
                ]
            );
        } else {
            $this->tableGateway->insert($resultSet->getHydrator()->extract($model));
        }

        return $model;
    }

    protected function doDeleteModel($model): void
    {
        if (method_exists($this->tableGateway, 'delete')) {
            $this->tableGateway->delete([
                "{$this->idField} = ?" => $this->getModelId($model),
            ]);
        }
    }

    protected function doGetIdentity(object $model): IdentityInterface
    {
        $id = $this->getModelId($model);

        if (! $id) {
            throw new LogicException('The model must be persisted before usage of this method');
        }

        return new Identity($id, $model);
    }

    /**
     * @param T $model
     */
    protected function getModelId(object $model): mixed
    {
        $rp = new ReflectionProperty($model, $this->idField);
        $rp->setAccessible(true);
        $id = $rp->getValue($model);
        $rp->setAccessible(false);

        return $id;
    }
}
