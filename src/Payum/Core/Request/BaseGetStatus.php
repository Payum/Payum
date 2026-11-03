<?php

namespace Payum\Core\Request;

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Read the Payum\Core\Result\PaymentStatus
 *             from the status property of the Payum\Core\Result\Result a command returns. A handler
 *             declares it with Payum\Core\Result\CaptureResult::captured() and friends.
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
