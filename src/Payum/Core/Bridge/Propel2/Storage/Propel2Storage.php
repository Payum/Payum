<?php
namespace Payum\Core\Bridge\Propel2\Storage;

use Payum\Core\Model\Identity;
use Payum\Core\Storage\AbstractStorage;

class Propel2Storage extends AbstractStorage
{
    /**
     * @var string
     */
    protected $modelQuery;

    /**
     * @param string $modelClass
     * @param string $modelQuery
     */
    public function __construct($modelClass, $modelQuery = null)
    {
        parent::__construct($modelClass);

        $this->modelQuery = $modelQuery ?: $modelClass . 'Query';
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
    protected function doGetIdentity($model)
    {
        return new Identity($model->getId(), $model);
    }

    /**
     * {@inheritDoc}
     */
    protected function doFind($id)
    {
        $query = new $this->modelQuery();
        $model = $query->findPk($id);

        return $model ?: new $this->modelClass();
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria)
    {
        $query = new $this->modelQuery();

        foreach ($criteria as $column => $value) {
            $query->filterBy($column, $value);
        }

        return $query->find();
    }
}
