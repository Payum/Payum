<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Request\Api;

use Payum\Core\Request\BaseModelAware;

class GetTransactionDetails extends BaseModelAware
{
    /**
     * @var int
     */
    protected $paymentRequestN;

    /**
     * @param mixed $model
     * @param int $paymentRequestN
     */
    public function __construct($model, $paymentRequestN)
    {
        parent::__construct($model);
        
        $this->paymentRequestN = $paymentRequestN;
    }

    /**
     * @return int
     */
    public function getPaymentRequestN()
    {
        return $this->paymentRequestN;
    }
}