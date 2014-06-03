<?php
namespace Payum\Bundle\PayumBundle\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Symfony\Request\ResponseInteractiveRequest;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Request\ObtainCreditCardRequest;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class ObtainCreditCardAction implements ActionInterface
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var Request
     */
    protected $httpRequest;

    /**
     * @param FormFactoryInterface $formFactory
     * @param EngineInterface      $templating
     */
    public function __construct(FormFactoryInterface $formFactory, EngineInterface $templating)
    {
        $this->formFactory = $formFactory;
        $this->templating = $templating;
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
     */
    public function execute($request)
    {
        /** @var $request ObtainCreditCardRequest */
        if (!$this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        if (!$this->httpRequest) {
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

        throw new ResponseInteractiveRequest(new Response(
            $this->templating->render('PayumBundle:Action:obtainCreditCard.html.twig', array(
                'form' => $form->createView()
            )),
            200,
            array(
                'Cache-Control' => 'no-store, no-cache, max-age=0, post-check=0, pre-check=0',
                'Pragma' => 'no-cache',
            )
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof ObtainCreditCardRequest;
    }

    /**
     * @return FormInterface
     */
    protected function createCreditCardForm()
    {
        return $this->formFactory->create('payum_credit_card');
    }
}