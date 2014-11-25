<?php
namespace Payum\Bitcoind\Action\Api;

use Nbobtc\Bitcoind\BitcoindInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;

abstract class BaseApiAction implements  ActionInterface, ApiAwareInterface
{
    /**
     * @var BitcoindInterface
     */
    protected $bitcoind;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof BitcoindInterface) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->bitcoind = $api;
    }
}