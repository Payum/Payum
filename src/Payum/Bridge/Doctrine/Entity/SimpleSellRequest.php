<?php
namespace Payum\Bridge\Doctrine\Entity;

use Payum\Request\SimpleSellRequest as BaseSimpleSellRequest;

class SimpleSellRequest extends BaseSimpleSellRequest
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}