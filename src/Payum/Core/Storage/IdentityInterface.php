<?php
namespace Payum\Core\Storage;

interface IdentityInterface extends \Serializable
{
    public function getClass(): string;

    public function getId(): mixed;
}
