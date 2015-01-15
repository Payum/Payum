<?php
namespace Payum\Core\Bridge\Zend\Storage;

use Payum\Core\Exception\LogicException;
use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;
use Zend\Db\TableGateway\TableGateway;

/**
 * A Storage class for use with Payum's StorageExtension, which uses ZendFramework's TableGateway database abstraction
 * classes. The bulk of the work is handled by the TableGateway passed in via the constructor. This can be configured
 * with hydrators and an object prototype (in a HydratingResultSet) to control what model is returned / handled and how
 * it is persisted in the database.
 *
 * E.g. Your service factory could be as follows (values in <chevrons> may need to be altered for your implementation
 * and you may wish to replace fully qualified classnames with aliases and use statements for brevity and readability):
 *    [
 *        'storageDetail' => function ($sm) {
 *            return new \Payum\Bridge\ZendTableGateway\Storage\ZendTableGateway(
 *                $sm->get('storageDetailTableGateway'),  // Your configured table gateway (defined below).
 *                '<Payment\Model\Transaction>'           // The classname of the entity that represents your data.
 *            );
 *        },
 *        'storageDetailTableGateway' => function ($sm) {
 *            return new \Zend\Db\TableGateway\TableGateway(
 *                '<transaction>',                        // Your database table name.
 *                $sm->get('<Zend\Db\Adapter\Adapter>'),  // Your configured database adapter.
 *                null,
 *                new HydratingResultSet(
 *                    $sm->get('<transactionHydrator>'),  // Hydrator to hydrate your data entity (defined below).
 *                    $sm->get('<transactionEntity>')     // The entity that represents your data (defined below).
 *                 )
 *            );
 *        },
 *        'transactionHydrator' => function ($sm) {
 *            return new <\Zend\Stdlib\Hydrator\ClassMethods()>;
 *        },
 *        'transactionEntity' => function ($sm) {
 *            return new <\Payment\Model\Transaction()>;  // The entity that represents your data (usually matching the
 *                                                           string passed in parameter two of ZendTableGateway above).
 *        },
 *    ]
 */
class TableGatewayStorage extends AbstractStorage
{
    /**
     * @var TableGateway
     */
    protected $tableGateway;

    /**
     * @var string
     */
    protected $idField;

    /**
     * @param TableGateway $tableGateway
     * @param string       $modelClass
     * @param string       $idField
     */
    public function __construct(TableGateway $tableGateway, $modelClass, $idField = 'id')
    {
        parent::__construct($modelClass);

        $this->tableGateway = $tableGateway;
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
