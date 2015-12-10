<?php
namespace Payum\Core\Extension;

interface ExtensionInterface
{
    /**
     * @var Context $context
     */
    public function onPreExecute(Context $context);

    /**
     * @var Context $context
     */
    public function onExecute(Context $context);

    /**
     * @var Context $context
     */
    public function onPostExecute(Context $context);
}
