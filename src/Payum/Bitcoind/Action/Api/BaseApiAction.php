<?php
namespace Payum\Bitcoind\Action\Api;

use Nbobtc\Bitcoind\Bitcoind;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;

abstract class BaseApiAction implements  ActionInterface, ApiAwareInterface
{
    /**
     * @var Bitcoind
     */
    protected $bitcoind;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Bitcoind) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->bitcoind = $api;
    }
}