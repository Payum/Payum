<?php

namespace Payum\Core\Extension;

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
