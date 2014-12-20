<?php
namespace Payum\Core\Storage;

interface IdentityInterface extends \Serializable
{
    /**
     * @return string
     */
    public function getClass();

    /**
     * @return mixed
     */
    public function getId();
}
