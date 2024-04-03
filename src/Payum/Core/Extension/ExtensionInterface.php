<?php

namespace Payum\Core\Extension;

interface ExtensionInterface
{
    /**
     * @var Context
     */
    public function onPreExecute(Context $context);

    /**
     * @var Context
     */
    public function onExecute(Context $context);

    /**
     * @var Context
     */
    public function onPostExecute(Context $context);
}
