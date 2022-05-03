<?php
namespace Payum\Core\Extension;

class ExtensionCollection implements ExtensionInterface
{
    /**
     * @var ExtensionInterface[]
     */
    protected array $extensions = [];

    public function addExtension(ExtensionInterface $extension, bool $forcePrepend = false): void
    {
        $forcePrepend ?
            array_unshift($this->extensions, $extension) :
            array_push($this->extensions, $extension)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function onPreExecute(Context $context): void
    {
        foreach ($this->extensions as $extension) {
            $extension->onPreExecute($context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute(Context $context): void
    {
        foreach ($this->extensions as $extension) {
            $extension->onExecute($context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute(Context $context): void
    {
        foreach ($this->extensions as $extension) {
            $extension->onPostExecute($context);
        }
    }
}
