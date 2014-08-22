<?php
namespace Payum\Klarna\Invoice\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Klarna\Invoice\Config;

abstract class BaseApiAwareAction implements  ApiAwareInterface, ActionInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @param mixed $api
     *
     * @throws UnsupportedApiException if the given Api is not supported.
     */
    public function setApi($api)
    {
        if (false == $api instanceof Config) {
            throw new UnsupportedApiException('Instance of Config is expected to be passed as api.');
        }

        $this->config = $api;
    }

    /**
     * @return \Klarna
     */
    protected function createKlarna()
    {
        $klarna = new \Klarna;

        $klarna->config(
            $this->config->eid,
            $this->config->secret,
            $this->config->country,
            $this->config->language,
            $this->config->currency,
            $this->config->mode
        );

        $rp = new \ReflectionProperty($klarna, 'xmlrpc');
        $rp->setAccessible(true);
        /** @var \xmlrpc_client $xmlrpc */
        $xmlrpc = $rp->getValue($klarna);
        $xmlrpc->verifyhost = 0;
        $xmlrpc->verifypeer = false;
        $rp->setAccessible(false);

        return $klarna;
    }

    /**
     * @param \ArrayAccess $details
     * @param \KlarnaException $e
     * @param object $request
     */
    protected function populateDetailsWithError(\ArrayAccess $details, \KlarnaException $e, $request)
    {
        $details['error_request'] = get_class($request);
        $details['error_file'] = $e->getFile();
        $details['error_line'] = $e->getLine();
        $details['error_code'] = $e->getCode();
        $details['error_message'] = $e->getMessage();
    }
}
