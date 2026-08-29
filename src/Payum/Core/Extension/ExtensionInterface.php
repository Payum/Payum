<?php

namespace Payum\Core\Extension;

/**
 * @deprecated since 2.0.0, will be removed in 3.0. A cross-cutting concern on the command path is a
 *             Payum\Core\Middleware\MiddlewareInterface, which wraps the whole execution rather than
 *             observing it at three points.
 */
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
