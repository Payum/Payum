<?php
namespace Payum\Core;

use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Extension\ExtensionCollection;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\Request\InteractiveRequestInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;

class Payment implements PaymentInterface
{
    /**
     * @var \Payum\Core\Action\ActionInterface[]
     */
    protected $actions = array();

    /**
     * @var mixed[]
     */
    protected $apis = array();

    /**
     * @var \Payum\Core\Extension\ExtensionCollection
     */
    protected $extensions;

    /**
     */
    public function __construct()
    {
        $this->extensions = new ExtensionCollection;
    }

    /**
     * {@inheritDoc}
     */
    public function addApi($api, $forcePrepend = false)
    {
        $forcePrepend ?
            array_unshift($this->apis, $api) :
            array_push($this->apis, $api)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function addAction(ActionInterface $action, $forcePrepend = false)
    {
        if ($action instanceof PaymentAwareInterface) {
            $action->setPayment($this);
        }

        if ($action instanceof ApiAwareInterface) {
            $apiSet = false;
            foreach ($this->apis as $api) {
                try {
                    $action->setApi($api);
                    $apiSet = true;
                    break;
                } catch (UnsupportedApiException $e) {}
            }

            if (false == $apiSet) {
                throw new LogicException(sprintf(
                    'Cannot find right api supported by %s',
                    get_class($action)
                ));
            }
        }

        $forcePrepend ?
            array_unshift($this->actions, $action) :
            array_push($this->actions, $action)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function addExtension(ExtensionInterface $extension, $forcePrepend = false)
    {
        $this->extensions->addExtension($extension, $forcePrepend);
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request, $catchInteractive = false)
    {
        $action = null;
        try {
            $this->extensions->onPreExecute($request);

            if (false == $action = $this->findActionSupported($request)) {
                throw RequestNotSupportedException::create($request);
            }

            $this->extensions->onExecute($request, $action);

            $action->execute($request);

            $this->extensions->onPostExecute($request, $action);
        } catch (InteractiveRequestInterface $interactiveRequest) {
            $interactiveRequest =
                $this->extensions->onInteractiveRequest($interactiveRequest, $request, $action) ?:
                $interactiveRequest
            ;

            if ($catchInteractive) {
                return $interactiveRequest;
            }

            throw $interactiveRequest;
        } catch (\Exception $e) {
            $this->extensions->onException($e, $request, $action);

            throw $e;
        }

        return null;
    }

    /**
     * @param mixed $request
     *
     * @return ActionInterface|false
     */
    protected function findActionSupported($request)
    {
        foreach ($this->actions as $action) {
            if ($action->supports($request)) {
                return $action;
            }
        }

        return false;
    }
}
