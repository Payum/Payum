<?php

namespace Payum\Core\Extension;

use Payum\Core\Exception\LogicException;

/**
 * @deprecated since 2.0.0, will be removed in 3.0. Use
 *             Payum\Core\Middleware\EndlessCycleDetectorMiddleware instead.
 */
class EndlessCycleDetectorExtension implements ExtensionInterface
{
    /**
     * @var int
     */
    protected $limit;

    /**
     * @param int $limit
     */
    public function __construct($limit = 100)
    {
        $this->limit = $limit;
    }

    public function onPreExecute(Context $context): void
    {
        if (count($context->getPrevious()) >= $this->limit) {
            throw new LogicException(sprintf(
                'Possible endless cycle detected. ::onPreExecute was called %d times before reach the limit.',
                $this->limit
            ));
        }
    }

    public function onExecute(Context $context): void
    {
    }

    public function onPostExecute(Context $context): void
    {
    }
}
