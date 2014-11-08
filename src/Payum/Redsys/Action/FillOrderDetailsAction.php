<?php
namespace Payum\Redsys\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Redsys\Api;

class FillOrderDetailsAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var \Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected $api;

    /**
     * @var GenericTokenFactoryInterface
     */
    protected $tokenFactory;

    /**
     * @param GenericTokenFactoryInterface $tokenFactory
     */
    public function __construct( GenericTokenFactoryInterface $tokenFactory = null )
    {
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function setApi( $api )
    {
        if (false == $api instanceof Api) {
            throw new UnsupportedApiException( 'Not supported.' );
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     *
     * @param FillOrderDetails $request
     */
    public function execute( $request )
    {
        RequestNotSupportedException::assertSupports( $this, $request );

        $order = $request->getOrder();
        $token = $request->getToken();
        $details = $this->api->preparePayment( $order, $token );
        $order->setDetails( $details );
    }

    /**
     * {@inheritDoc}
     */
    public function supports( $request )
    {
        return $request instanceof FillOrderDetails;
    }


}
