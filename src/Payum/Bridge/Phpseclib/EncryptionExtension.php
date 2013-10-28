<?php
namespace Payum\Bridge\Phpseclib;

use Payum\Action\ActionInterface;
use Payum\Extension\ExtensionInterface;
use Payum\Request\InteractiveRequestInterface;
use Payum\Request\ModelRequestInterface;

class EncryptionExtension implements ExtensionInterface
{
    /**
     * @var \Crypt_Base
     */
    protected $crypt;

    public function __construct(\Crypt_Base $crypt)
    {
        $this->crypt = $crypt;
    }

    /**
     * {@inheritDoc}
     */
    public function onPreExecute($request)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute($request, ActionInterface $action)
    {
        if (false == $request instanceof ModelRequestInterface) {
            return;
        }
        if (false == $request->getModel() instanceof \ArrayAccess) {

        }

        $model = $request->getModel();
        if (false == $model['encrypted']) {
            return;
        }

        foreach ($model as $name => $value) {
            $model[$name] = $this->crypt->decrypt($value);
        }

        $model['encrypted'] = false;
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute($request, ActionInterface $action)
    {
        $this->onPostXXX($request);
    }

    /**
     * {@inheritDoc}
     */
    public function onInteractiveRequest(InteractiveRequestInterface $interactiveRequest, $request, ActionInterface $action)
    {
        $this->onPostXXX($request);
    }

    /**
     * {@inheritDoc}
     */
    public function onException(\Exception $exception, $request, ActionInterface $action = null)
    {
        $this->onPostXXX($request);
    }

    protected function onPostXXX($request)
    {
        if (false == $request instanceof ModelRequestInterface) {
            return;
        }
        if (false == $request->getModel() instanceof \ArrayAccess) {

        }

        $model = $request->getModel();
        if ($model['encrypted']) {
            return;
        }

        foreach ($model as $name => $value) {
            $model[$name] = $this->crypt->encrypt($value);
        }

        $model['encrypted'] = true;
    }
}