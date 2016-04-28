<?php
namespace Payum\Paypal\ProHosted\Request\Api;

use Payum\Core\Request\Generic;

class GetTransactionDetails extends Generic
{
    /**
     * @var int
     */
    protected $paymentRequestN;

    /**
     * @param mixed $model
     * @param int   $paymentRequestN
     */
    public function __construct($model, $paymentRequestN = null)
    {
        parent::__construct($model);

        $this->paymentRequestN = $paymentRequestN;
    }

    /**
     * @return string
     */
    public function getPaymentRequestN()
    {
        return $this->paymentRequestN;
    }
}
