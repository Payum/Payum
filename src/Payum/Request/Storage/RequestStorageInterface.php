<?php
namespace Payum\Request\Storage;

interface RequestStorageInterface
{
    /**
     * @return mixed
     */
    function createRequest();

    /**
     * @param mixed $request
     *
     * @return void
     */
    function updateRequest($request);

    /**
     * @param mixed $id
     * 
     * @return mixed
     */
    function findRequestById($id);
}