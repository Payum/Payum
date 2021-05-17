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
        $requestEnvelope = $model->getArray('requestEnvelope');

        if (false == $requestEnvelope['detailLevel']) {
            $requestEnvelope['detailLevel'] = Api::DETAIL_LEVEL_RETURN_ALL;

            $model['requestEnvelope'] = (array) $requestEnvelope;
        }
    }

    /**
     * @param ArrayObject $model
     */
    protected function setDefaultErrorLanguage(ArrayObject $model)
    {
        $requestEnvelope = $model->getArray('requestEnvelope');

        if (false == $requestEnvelope['errorLanguage']) {
            $requestEnvelope['errorLanguage'] = Api::ERROR_LANGUAGE_EN_US;

            $model['requestEnvelope'] = (array) $requestEnvelope;
        }
    }
}
