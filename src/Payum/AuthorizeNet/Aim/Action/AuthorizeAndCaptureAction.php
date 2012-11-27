<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Action\ActionInterface;
use Payum\Exception\RequestNotSupportedException;
use Payum\AuthorizeNet\Aim\Request\AuthorizeAndCaptureRequest;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;

class AuthorizeAndCaptureAction implements ActionInterface
{
    /**
     * @var \AuthorizeNetAIM
     */
    protected $authorizeNetAim;

    /**
     * @param \AuthorizeNetAIM $authorizeNetAim
     */
    public function __construct(AuthorizeNetAIM $authorizeNetAim)
    {
        $this->authorizeNetAim = $authorizeNetAim;
    }
    
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request AuthorizeAndCaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $authorizeNetAim = clone $this->authorizeNetAim;

        $request->getInstruction()->fillRequest($authorizeNetAim);
        
        $response = $authorizeNetAim->authorizeAndCapture();
        
        $request->getInstruction()->updateFromResponse($response);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof AuthorizeAndCaptureRequest;
    }
}