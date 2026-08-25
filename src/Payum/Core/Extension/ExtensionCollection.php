<?php

namespace Payum\Core\Extension;

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Use
 *             Payum\Core\Middleware\MiddlewareCollection instead.
 */
class ExtensionCollection implements ExtensionInterface
{
    /**
     * @var ExtensionInterface[]
     */
    protected $extensions = [];

    /**
     * @param bool               $forcePrepend
     */
    public function addExtension(ExtensionInterface $extension, $forcePrepend = false): void
    {
        $forcePrepend ?
            array_unshift($this->extensions, $extension) :
            array_push($this->extensions, $extension)
        ;
    }

    public function onPreExecute(Context $context): void
    {
        foreach ($this->extensions as $extension) {
            $extension->onPreExecute($context);
        }
    }

    public function onExecute(Context $context): void
    {
        foreach ($this->extensions as $extension) {
            $extension->onExecute($context);
        }
    }

    public function onPostExecute(Context $context): void
    {
        foreach ($this->extensions as $extension) {
            $extension->onPostExecute($context);
        }
    }
}
