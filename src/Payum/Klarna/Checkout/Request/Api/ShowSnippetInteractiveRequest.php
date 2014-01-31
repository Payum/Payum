<?php
namespace Payum\Klarna\Checkout\Request\Api;

use Payum\Core\Request\BaseModelInteractiveRequest;

class ShowSnippetInteractiveRequest extends BaseModelInteractiveRequest
{
    public function __construct(\Klarna_Checkout_Order $order)
    {
        parent::__construct($order);
    }

    /**
     * @return string
     */
    public function getSnippet()
    {
        $gui = $this->model['gui'];

        return $gui['snippet'];
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->model['gui']['layout'];
    }
} 