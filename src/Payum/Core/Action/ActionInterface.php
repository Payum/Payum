<?php

namespace Payum\Core\Action;

use Payum\Core\Exception\RequestNotSupportedException;

interface ActionInterface
{
    /**
     * @throws RequestNotSupportedException if the action dose not support the request.
     */
    public function execute(mixed $request): void;

    public function supports(mixed $request): bool;
}
