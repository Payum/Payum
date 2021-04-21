<?php
namespace Payum\Core\Extension;

class ExtensionCollection implements ExtensionInterface
{
    /**
     * @var ExtensionInterface[]
     */
    protected $extensions = array();

    /**
     * @param ExtensionInterface $extension
     * @param bool               $forcePrepend
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
     * {@inheritDoc}
     */
    public function onPreExecute(Context $context)
    {
        foreach ($this->extensions as $extension) {
            $extension->onPreExecute($context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute(Context $context)
    {
        foreach ($this->extensions as $extension) {
            $extension->onExecute($context);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute(Context $context)
    {
        foreach ($this->extensions as $extension) {
            $extension->onPostExecute($context);
        }
    }
}
