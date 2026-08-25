<?php

namespace Payum\Core\Request;

/**
 * @deprecated since 2.0.0, will be removed in 3.0. A handler declares the status on its result —
 *             Payum\Core\Result\CaptureResult::captured() and friends — and it is read back as a
 *             Payum\Core\Result\PaymentStatus.
 */
abstract class BaseGetStatus extends Generic implements GetStatusInterface
{
    /**
     * @var int
     */
    protected $status;

    public function __construct($model)
    {
        parent::__construct($model);

        $this->markUnknown();
    }

    public function getValue()
    {
        return $this->status;
    }
}
