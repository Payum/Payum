<?php
namespace Payum\Paypal\AdaptivePayments\Json\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;
use Payum\Paypal\AdaptivePayments\Json\Api;
use Payum\Paypal\AdaptivePayments\Json\Request\Api\AuthorizeKey;

/**
 * @property Api $api
 */
class AuthorizeKeyAction extends BaseAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request AuthorizeKey */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        if ($model['payKey']) {
            $local = $model->getArray('local');
            if ($local['embedded']) {
                $url = $this->api->generateEmbeddedPayKeyAuthorizationUrl($model['payKey']);
            } else {
                $url = $this->api->generatePayKeyAuthorizationUrl($model['payKey']);
            }
        } elseif ($model['preapprovalKey']) {
            $url = $this->api->generatePreApprovalAuthorizationUrl($model['preapprovalKey']);
        } else {
            throw new LogicException('The "payKey" is required for explicit approval, or the "preapprovalKey" for a pre-approval payment.');
        }

        throw new HttpRedirect($url);
    }


    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof AuthorizeKey &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
