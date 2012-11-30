<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Action\ActionInterface;
use Payum\Request\UserInputRequiredInteractiveRequest;
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

        $instruction = $request->getInstruction();
        if (false == ($instruction->getAmount() && $instruction->getCardNum() && $instruction->getExpDate())) {
            throw new UserInputRequiredInteractiveRequest(array('amount', 'card_num', 'exp_date'));
        }

        $authorizeNetAim = clone $this->authorizeNetAim;

        $instruction->fillRequest($authorizeNetAim);
        $instruction->updateFromResponse($authorizeNetAim->authorizeAndCapture());
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof AuthorizeAndCaptureRequest;
    }
}