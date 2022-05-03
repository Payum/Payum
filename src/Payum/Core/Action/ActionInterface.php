<?php
namespace Payum\Core\Action;

interface ActionInterface
{
    /**
     * @throws \Payum\Core\Exception\RequestNotSupportedException if the action does not support the request.
     */
    public function execute(mixed $request): void;

    public function supports(mixed $request): bool;
}
