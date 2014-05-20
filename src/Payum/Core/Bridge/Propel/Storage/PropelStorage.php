<?php
namespace Payum\Core\Bridge\Propel\Storage;

use Payum\Core\Storage\AbstractStorage;
use Payum\Core\Model\Identificator;

class PropelStorage extends AbstractStorage
{
    /**
     * @var string
     */
    protected $modelQuery;
    /**
     * @var string
     */
    protected $modelPeer;

    /**
     * @param string $modelClass
     */
    public function __construct($modelClass)
    {
        parent::__construct($modelClass);

        $this->modelQuery = $modelClass . 'Query';
    }

    /**
     * {@inheritDoc}
     */
    public function findModelById($id)
    {
        $modelQuery = $this->modelQuery;
        return $modelQuery::create()
            ->findPk($id);
    }

    /**
     * {@inheritDoc}
     */
    protected function doUpdateModel($model)
    {
        $model->save();
    }

    /**
     * {@inheritDoc}
     */
    protected function doDeleteModel($model)
    {
        $model->delete();
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetIdentificator($model)
    {
        $id = $this->getModelId($model);

        if (count($id) > 1) {
            throw new \LogicException('Storage not support composite primary ids');
        }

        return new Identificator(array_shift($id), $model);
    }

    /**
     * @param object $model
     *
     * @return mixed
     */
    protected function getModelId($model)
    {
        $id = array();
        $modelPeer = get_class($model) . 'Peer';
        $modelColumns = $modelPeer::getTableMap()->getColumns();
        foreach ($modelColumns as $column) {
            if ($column->isPrimaryKey()) {
                $name = $column->getPhpName();
                $id[$name] =
                    $model->getByName($name); // looks for phpName by default
            }
        }

        return $id;
    }
}
