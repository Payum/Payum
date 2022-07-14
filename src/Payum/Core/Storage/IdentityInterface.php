<?php

namespace Payum\Core\Storage;

use Serializable;

/**
 * @template-covariant T of object
 */
interface IdentityInterface extends Serializable
{
    /**
     * @return class-string<T>
     */
    public function getClass(): string;

    public function getId(): mixed;
}
