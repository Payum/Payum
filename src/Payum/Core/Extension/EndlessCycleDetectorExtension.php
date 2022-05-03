<?php
namespace Payum\Core\Extension;

use Payum\Core\Exception\LogicException;

class EndlessCycleDetectorExtension implements ExtensionInterface
{
    public function __construct(protected int $limit = 100)
    {}

    /**
     * {@inheritDoc}
     */
    public function onPreExecute(Context $context): void
    {
        if (count($context->getPrevious()) >= $this->limit) {
            throw new LogicException(sprintf(
                'Possible endless cycle detected. ::onPreExecute was called %d times before reach the limit.',
                $this->limit
            ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute(Context $context): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute(Context $context): void
    {
    }
}
