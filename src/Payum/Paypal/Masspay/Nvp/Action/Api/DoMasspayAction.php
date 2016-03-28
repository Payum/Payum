<?php
namespace Payum\Paypal\Masspay\Nvp\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\Masspay\Nvp\Api;
use Payum\Paypal\Masspay\Nvp\Request\Api\DoMasspay;

/**
 * @property Api $api
 */
class DoMasspayAction implements ActionInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }
    
    /**
     * {@inheritdoc}
     * 
     * @param DoMasspay $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->replace(
            $this->api->massPay((array) $model)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof DoMasspay &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}