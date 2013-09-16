<?php
namespace Payum\Registry;

class SimpleRegistry extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function getService($id)
    {
        return $id;
    }
}