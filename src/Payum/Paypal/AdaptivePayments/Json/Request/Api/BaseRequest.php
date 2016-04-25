<?php
namespace Payum\Paypal\AdaptivePayments\Json\Request\Api;

use Payum\Core\Request\Generic;

class BaseRequest extends Generic
{
    protected $response;

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }
}