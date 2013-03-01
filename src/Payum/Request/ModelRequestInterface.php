<?php
namespace Payum\Request;

interface ModelRequestInterface 
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