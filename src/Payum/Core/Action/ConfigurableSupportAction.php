<?php
namespace Payum\Core\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Support;

class ConfigurableSupportAction implements ActionInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     * 
     * @param Support $support
     */
    public function execute($support)
    {
        RequestNotSupportedException::assertSupports($this, $support);
        $support->setSupported(false);

        $request = $support->getRequest();
        foreach ($this->config as $requestClass => $models) {
            if ($request instanceof $requestClass) {
                foreach ($models as $model) {
                    if ($request->getModel() instanceof $model) {
                        $support->setSupported(true);

                        return;
                    } else if ($request->getModel() == $model) {
                        $support->setSupported(true);

                        return;
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof Support;
    }
}