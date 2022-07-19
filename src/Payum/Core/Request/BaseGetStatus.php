<?php

namespace Payum\Core\Request;

abstract class BaseGetStatus extends Generic implements GetStatusInterface
{
    protected int|string $status;

    public function __construct(mixed $model)
    {
        parent::__construct($model);

        $this->markUnknown();
    }

    public function getValue(): int|string
    {
        return $this->status;
    }
}
