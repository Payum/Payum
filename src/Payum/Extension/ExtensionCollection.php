<?php
namespace Payum\Extension;

use Payum\Action\ActionInterface;
use Payum\Request\InteractiveRequestInterface;

class ExtensionCollection implements ExtensionInterface 
{
    /**
     * @var ExtensionInterface[]
     */
    protected $extensions = array();

    /**
     * @param ExtensionInterface $extension
     * @param bool $forcePrepend
     * 
     * @return void
     */
    public function addExtension(ExtensionInterface $extension, $forcePrepend = false)
    {
        $forcePrepend ? 
            array_unshift($this->extensions, $extension) : 
            array_push($this->extensions, $extension)
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function onPreExecute($request)
    {
        foreach ($this->extensions as $extension) {
            $extension->onPreExecute($request);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onExecute($request, ActionInterface $action)
    {
        foreach ($this->extensions as $extension) {
            $extension->onExecute($request, $action);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onPostExecute($request, ActionInterface $action)
    {
        foreach ($this->extensions as $extension) {
            $extension->onPostExecute($request, $action);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onInteractiveRequest(InteractiveRequestInterface $interactiveRequest, $request, ActionInterface $action)
    {
        $inputInteractiveRequest = $interactiveRequest;
        foreach ($this->extensions as $extension) {
            if (null !== $newInteractiveRequest = $extension->onInteractiveRequest($interactiveRequest, $request, $action)) {
                $interactiveRequest = $newInteractiveRequest;
            }
        }

        return $inputInteractiveRequest !== $interactiveRequest ? $interactiveRequest : null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function onException(\Exception $exception, $request, ActionInterface $action = null)
    {
        foreach ($this->extensions as $extension) {
            $extension->onException($exception, $request, $action);
        }
    }
}