<?php
namespace Payum\Core\Tests\Mocks\Model;

use Payum\Core\Exception\LogicException;

class Propel2ModelQuery
{
    const MODEL_CLASS = "Payum\\Core\\Tests\\Mocks\\Model\\Propel2Model";

    protected $filters = array();

    protected $modelReflection;

    public function __construct()
    {
        $this->modelReflection = new \ReflectionClass(static::MODEL_CLASS);
    }

    public function filterBy($column, $value)
    {
        if (!$this->modelReflection->hasProperty($column)) {
            throw new LogicException(sprintf(
                "The model doesn't have the column '%s'",
                $column
            ));
        }

        $this->filters[] = array($column, $value);

        return $this;
    }

    public function find()
    {
        $model = new Propel2Model();
        $this->applyFilters($model);

        return $model;
    }

    public function findPk($id)
    {
        $model = new Propel2Model();
        $model->setId($id);

        return $model;
    }

    protected function applyFilters(Propel2Model $model)
    {
        foreach ($this->filters as $filter) {
            $propriety = new \ReflectionProperty(static::MODEL_CLASS, $filter[0]);
            $propriety->setAccessible(true);

            $propriety->setValue($model, $filter[1]);
        }
    }
}
