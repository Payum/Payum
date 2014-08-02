<?php
namespace Payum\Core\Reply;

interface ModelAwareInterface
{
    /**
     * @param mixed $model
     * 
     * @return void
     */
    function setModel($model);

    /**
     * @return mixed
     */
    function getModel();
}