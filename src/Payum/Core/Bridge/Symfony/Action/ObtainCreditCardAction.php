<?php
namespace Payum\Core\Bridge\Symfony\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Symfony\Reply\HttpResponse;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Request\RenderTemplate;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ObtainCreditCardAction extends GatewayAwareAction
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var Request
     */
    protected $httpRequest;

    /**
     * @var string
     */
    protected $templateName;

    /**
     * @param FormFactoryInterface $formFactory
     * @param string               $templateName
     */
    public function __construct(FormFactoryInterface $formFactory, $templateName)
    {
        $this->formFactory = $formFactory;
        $this->templateName = $templateName;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->httpRequest = $request;
    }

    /**
     * {@inheritDoc}
     *
     * @param ObtainCreditCard $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        if (false == $this->httpRequest) {
            throw new LogicException('The action can be run only when http request is set.');
        }

        $form = $this->createCreditCardForm();

        $form->handleRequest($this->httpRequest);
        if ($form->isSubmitted()) {
            /** @var CreditCardInterface $card */
            $card = $form->getData();
            $card->secure();

            if ($form->isValid()) {
                $request->set($card);

                return;
            }
        }

        $renderTemplate = new RenderTemplate($this->templateName, array(
            'model' => $request->getModel(),
            'firstModel' => $request->getFirstModel(),
            'form' => $form->createView(),
            'actionUrl' => $request->getToken() ? $request->getToken()->getTargetUrl() : null,
        ));
        $this->gateway->execute($renderTemplate);

        throw new HttpResponse(new Response($renderTemplate->getResult(), 200, array(
            'Cache-Control' => 'no-store, no-cache, max-age=0, post-check=0, pre-check=0',
            'X-Status-Code' => 200,
            'Pragma' => 'no-cache',
        )));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof ObtainCreditCard;
    }

    /**
     * @return FormInterface
     */
    protected function createCreditCardForm()
    {
        return $this->formFactory->create('payum_credit_card');
    }
}
