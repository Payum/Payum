<?php
/**
 * Created by PhpStorm.
 * User: carlos
 * Date: 1/11/14
 * Time: 13:42
 */

namespace Payum\Redsys\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Redsys\Api;

class CaptureAction extends PaymentAwareAction implements ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * {@inheritDoc}
     */
    public function setApi( $api )
    {
        if (false === $api instanceof Api) {
            throw new UnsupportedApiException( 'Not supported.' );
        }
        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function execute( $request )
    {
        /** @var $request Capture */
        RequestNotSupportedException::assertSupports( $this, $request );

        $model = ArrayObject::ensureArrayObject( $request->getModel() );

        $httpRequest = new GetHttpRequest;
        $this->payment->execute( $httpRequest );

        //we are back from redsys site so we have to just update model.
        if (!empty( $httpRequest->request ) &&
            $this->api->validateGatewayResponse( $httpRequest->request )
        ) {
            $model->replace( $httpRequest->request );
            // throw empty response so bank receive a response with code 200 
            throw new HttpResponse( '' );
        }

        if (false == $model['Ds_Response']) {
            throw new HttpPostRedirect(
                $this->api->getRedsysUrl(),
                $this->api->addMerchantDataToPayment( $model->toUnsafeArray() )
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports( $request )
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }
} 
