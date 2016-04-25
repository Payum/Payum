<?php
namespace Payum\Paypal\AdaptivePayments\Json\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Paypal\AdaptivePayments\Json\Api;

abstract class BaseAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * @param ArrayObject $model
     */
    protected function setDefaultDetailLevel(ArrayObject $model)
    {
        if (false == isset($model['requestEnvelope']['detailLevel'])) {
            $model['requestEnvelope']['detailLevel'] = Api::DETAIL_LEVEL_RETURN_ALL;
        }
    }
}
