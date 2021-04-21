<?php
namespace Payum\Core\Extension;

use Payum\Core\Exception\LogicException;

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

    /**
     * {@inheritDoc}
     */
    public function onPreExecute(Context $context)
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
    public function onExecute(Context $context)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute(Context $context)
    {
    }
}
