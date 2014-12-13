<?php
namespace Payum\Core\Storage;

interface IdentityInterface extends \Serializable
{
    /**
     * @return string
     */
    function getClass();

    /**
     * @return mixed
     */
    function getId();
}