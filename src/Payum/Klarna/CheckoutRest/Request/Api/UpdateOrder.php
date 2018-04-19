<?php
namespace Payum\Klarna\CheckoutRest\Request\Api;

class UpdateOrder extends BaseOrderManagementOrder
{

    /**
     * @var string|null
     */
    private $merchantReference1;

    /**
     * @var string|null
     */
    private $merchantReference2;

    /**
     * @var bool
     */
    private $shouldAcknowledge = false;

    /**
     * @param string|null $merchantReference1
     * @param string|null $merchantReference2
     *
     * @return self
     */
    public function setMerchantReferences($merchantReference1, $merchantReference2)
    {
        $this->merchantReference1 = $merchantReference1;
        $this->merchantReference2 = $merchantReference2;

        return $this;
    }

    /**
     * Call this if you want to acknowledge the order
     *
     * @return self
     */
    public function acknowledgeOrder()
    {
        $this->shouldAcknowledge = true;

        return $this;
    }

    /**
     * Executes queued updates
     */
    public function execute()
    {
        if ($this->merchantReference1 !== null || $this->merchantReference2 !== null)
        {
            $merchantReferences = [];

            if ($this->merchantReference1 !== null)
            {
                $merchantReferences['merchant_reference1'] = $this->merchantReference1;
            }
            if ($this->merchantReference2 !== null)
            {
                $merchantReferences['merchant_reference2'] = $this->merchantReference2;
            }

            $this->getOrder()->updateMerchantReferences($merchantReferences);
        }

        if ($this->shouldAcknowledge === true)
        {
            $this->getOrder()->acknowledge();
        }
    }

}
