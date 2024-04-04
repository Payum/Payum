<?php

namespace Payum\Core\Bridge\Laminas\Storage;

use Payum\Core\Exception\LogicException;
use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;
use Laminas\Db\TableGateway\TableGateway as LaminasTableGateway;
use Zend\Db\TableGateway\TableGateway as ZendTableGateway;

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
 */
class TableGatewayStorage extends AbstractStorage
{
    /**
     * @var LaminasTableGateway|ZendTableGateway
     */
    protected $tableGateway;

    /**
     * @var string
     */
    protected $idField;

    /**
     * @param LaminasTableGateway|ZendTableGateway $tableGateway
     * @param string $modelClass
     * @param string $idField
     */
    public function __construct($tableGateway, $modelClass, $idField = 'id')
    {
        parent::__construct($modelClass);

        if ($tableGateway instanceof LaminasTableGateway) {
            $this->tableGateway = $tableGateway;
        } else if ($tableGateway instanceof ZendTableGateway) {
            @trigger_error(sprintf('Passing an instance of %s as the first argument to %s is deprecated and won\'t be supported in 2.0. Please using Laminas instead.', ZendTableGateway::class, self::class));
            $this->tableGateway = $tableGateway;
        } else {
            throw new \InvalidArgumentException(sprintf('Argument $tableGateway of %s must be an instance of %s or %s, %s given.', self::class, LaminasTableGateway::class, ZendTableGateway::class, (is_object($tableGateway) ? get_class($tableGateway) : gettype($tableGateway))));
        }

        $this->idField = $idField;
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria)
    {
        throw new LogicException('Method is not supported by the storage.');
    }

    /**
     * {@inheritDoc}
     */
    protected function doFind($id)
    {
        return $this->tableGateway->select(array("{$this->idField} = ?" => $id))->current();
    }

    /**
     * {@inheritDoc}
     */
    protected function doUpdateModel($model)
    {
        if ($id = $this->getModelId($model)) {
            $this->tableGateway->update(
                $this->tableGateway->getResultSetPrototype()->getHydrator()->extract($model),
                array("{$this->idField} = ?" => $id)
            );
        } else {
            $this->tableGateway->insert($this->tableGateway->getResultSetPrototype()->getHydrator()->extract($model));
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doDeleteModel($model)
    {
        $this->tableGateway->delete(array("{$this->idField} = ?" => $this->getModelId($model)));
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetIdentity($model)
    {
        $id = $this->getModelId($model);

        if (!$id) {
            throw new LogicException('The model must be persisted before usage of this method');
        }

        return new Identity($id, $model);
    }

    /**
     * @param object $model
     *
     * @return mixed
     */
    protected function getModelId($model)
    {
        $rp = new \ReflectionProperty($model, $this->idField);
        $rp->setAccessible(true);
        $id = $rp->getValue($model);
        $rp->setAccessible(false);

        return $id;
    }
}
