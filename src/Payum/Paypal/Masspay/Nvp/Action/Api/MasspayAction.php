<?php
namespace Payum\Paypal\Masspay\Nvp\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\Masspay\Nvp\Api;
use Payum\Paypal\Masspay\Nvp\Request\Api\Masspay;

/**
 * @property Api $api
 */
class MasspayAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }
    
    /**
     * {@inheritdoc}
     *
     * @param Masspay $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['ACK']) {
            throw new LogicException('Payout has already been acknowledged');
        }

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
            $request instanceof Masspay &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
