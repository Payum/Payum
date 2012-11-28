<?php
namespace Payum\Request\Storage;

interface RequestStorageInterface
{
    /**
     * @return mixed
     */
    function create();

    /**
     * @param mixed $request
     *
     * @return void
     */
    function update($request);

    /**
     * @param mixed $id
     *
     * @return mixed|null
     */
    function find($id);
}