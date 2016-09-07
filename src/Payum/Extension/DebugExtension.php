<?php
namespace Payum\Extension;

use Payum\Action\ActionInterface;
use Payum\Exception\ExceptionInterface;
use Payum\Request\InteractiveRequestInterface;
use Payum\Request\ModelRequestInterface;
use Payum\Request\RedirectUrlInteractiveRequest;

class DebugExtension implements ExtensionInterface
{
    /**
     * @var string[]
     */
    protected $executedRequests;

    /**
     * @var string
     */
    protected $currentRequest;

    /**
     * @var string[]
     */
    protected $stackLevel;

    /**
     * {@inheritDoc}
     */
    public function onPreExecute($request)
    {
        if ($this->stackLevel == 0) {
            $this->executedRequests = array();
            $this->currentRequest = '';
        }

        $this->stackLevel++;

        $this->currentRequest = sprintf(
            '%d# Payment::execute(%s)',
            $this->stackLevel,
            $this->toStringRequest($request)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute($request, ActionInterface $action)
    {
        $this->currentRequest = sprintf(
            '%d# %s::execute(%s)',
            $this->stackLevel,
            $this->toString($action, false),
            $this->toStringRequest($request)
        );

        array_push($this->executedRequests, $this->currentRequest);
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute($request, ActionInterface $action)
    {
        $this->stackLevel--;
    }

    /**
     * {@inheritDoc}
     */
    public function onInteractiveRequest(InteractiveRequestInterface $interactiveRequest, $request, ActionInterface $action)
    {
        $this->currentRequest = sprintf('%d# %s::execute(%s) throws interactive %s',
            $this->stackLevel,
            $this->toString($action),
            $this->toStringRequest($request),
            $this->toStringRequest($interactiveRequest)
        );

        array_push($this->executedRequests, $this->currentRequest);

        $this->stackLevel--;
    }

    /**
     * {@inheritDoc}
     */
    public function onException(\Exception $exception, $request, ActionInterface $action = null)
    {
        $this->currentRequest = sprintf('%d# %s::execute(%s) throws exception %s',
            $this->stackLevel,
            $action ? $this->toString($action) : 'Payment',
            $this->toStringRequest($request),
            $this->toString($exception)
        );

        array_push($this->executedRequests, $this->currentRequest);

        if ($exception instanceof ExceptionInterface && false == property_exists($exception, '_payum_debug_added')) {
            $newMessage = sprintf(
                "%s\n\n%s\n\n%s",
                $exception->getMessage(),
                implode("\n", $this->executedRequests),
                "Executed Requests stack was added by DebuggerExtension"
            );

            $rp = new \ReflectionProperty($exception, 'message');
            $rp->setAccessible(true);
            $rp->setValue($exception, $newMessage);
            $rp->setAccessible(false);

            $exception->_payum_debug_added = true;
        }

        $this->stackLevel--;
    }

    /**
     * @param mixed $request
     * @return string
     */
    protected function toStringRequest($request)
    {
        $message = $this->toString($request);
        if ($request instanceof ModelRequestInterface) {
            $message .= sprintf("{model: %s}", $this->toString($request->getModel()));
        }
        if ($request instanceof RedirectUrlInteractiveRequest) {
            $message .= sprintf('{url: %s}', $request->getUrl());
        }

        return $message;
    }

    /**
     * @param mixed $value
     * @param bool $shortClass
     *
     * @return string
     */
    protected function toString($value, $shortClass = true)
    {
        if (is_object($value)) {
            if ($shortClass) {
                $ro = new \ReflectionObject($value);

                return $ro->getShortName();
            }

            return get_class($value);
        }

        return gettype($value);
    }
}